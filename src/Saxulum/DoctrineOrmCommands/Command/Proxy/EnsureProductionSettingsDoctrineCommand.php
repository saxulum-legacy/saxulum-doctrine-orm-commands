<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saxulum\DoctrineOrmCommands\Command\Proxy;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;

/**
 * Ensure the Doctrine ORM is configured properly for a production environment.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class EnsureProductionSettingsDoctrineCommand extends EnsureProductionSettingsCommand
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param null            $name
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct($name = null, ManagerRegistry $managerRegistry)
    {
        parent::__construct($name);

        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:ensure-production-settings')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command');;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationEntityManager(
            $this->getApplication(),
            $this->managerRegistry,
            $input->getOption('em')
        );

        parent::execute($input, $output);
    }
}
