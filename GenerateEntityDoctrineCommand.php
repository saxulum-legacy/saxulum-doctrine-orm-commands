<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\DoctrineBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Initialize a new Doctrine entity inside a bundle.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class GenerateEntityDoctrineCommand extends DoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:generate:entity')
            ->setDescription('Generate a new Doctrine entity inside a bundle.')
            ->addArgument('bundle', InputArgument::REQUIRED, 'The bundle to initialize the entity in.')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity class to initialize.')
            ->addOption('mapping-type', null, InputOption::VALUE_OPTIONAL, 'The mapping type to to use for the entity.', 'xml')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'The fields to create with the new entity.')
            ->setHelp(<<<EOT
The <info>doctrine:generate:entity</info> task initializes a new Doctrine entity inside a bundle:

  <info>./app/console doctrine:generate:entity "Bundle\MyCustomBundle" "User\Group"</info>

The above would initialize a new entity in the following entity namespace <info>Bundle\MyCustomBundle\Entity\User\Group</info>.

You can also optionally specify the fields you want to generate in the new entity:

  <info>./app/console doctrine:generate:entity "Bundle\MyCustomBundle" "User\Group" --fields="name:string(255) description:text"</info>
EOT
        );
    }

    /**
     * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle\MySampleBundle")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!preg_match('/Bundle$/', $bundle = $input->getArgument('bundle'))) {
            throw new \InvalidArgumentException('The bundle name must end with Bundle. Example: "Bundle\MySampleBundle".');
        }

        $dirs = $this->container->get('kernel')->getBundleDirs();

        $tmp = str_replace('\\', '/', $bundle);
        $namespace = str_replace('/', '\\', dirname($tmp));
        $bundle = basename($tmp);

        if (!isset($dirs[$namespace])) {
            throw new \InvalidArgumentException(sprintf('Unable to initialize the bundle entity (%s not defined).', $namespace));
        }

        $entity = $input->getArgument('entity');
        $entityNamespace = $namespace.'\\'.$bundle.'\\Entity';
        $fullEntityClassName = $entityNamespace.'\\'.$entity;
        $mappingType = $input->getOption('mapping-type');

        $class = new ClassMetadataInfo($fullEntityClassName);
        $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
        $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);

        // Map the specified fields
        $fields = $input->getOption('fields');
        if ($fields) {
            $e = explode(' ', $fields);
            foreach ($e as $value) {
                $e = explode(':', $value);
                $name = $e[0];
                $type = isset($e[1]) ? $e[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;
                $class->mapField(array(
                    'fieldName' => $name,
                    'type' => $type,
                    'length' => $length
                ));
            }
        }

        // Setup a new exporter for the mapping type specified
        $cme = new ClassMetadataExporter();
        $exporter = $cme->getExporter($mappingType);

        if ('annotation' === $mappingType) {
            $path = $dirs[$namespace].'/'.$bundle.'/Entity/'.str_replace($entityNamespace.'\\', null, $fullEntityClassName).'.php';

            $exporter->setEntityGenerator($this->getEntityGenerator());
        } else {
            $mappingType = 'yaml' == $mappingType ? 'yml' : $mappingType;
            $path = $dirs[$namespace].'/'.$bundle.'/Resources/config/doctrine/metadata/orm/'.str_replace('\\', '.', $fullEntityClassName).'.dcm.'.$mappingType;
        }

        $code = $exporter->exportClassMetadata($class);

        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0777, true);
        }

        $output->writeln(sprintf('Generating entity for "<info>%s</info>"', $bundle));
        $output->writeln(sprintf('  > generating <comment>%s</comment>', $fullEntityClassName));
        file_put_contents($path, $code);
    }
}
