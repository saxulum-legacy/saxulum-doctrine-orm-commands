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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show information about mapped entities
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class InfoDoctrineCommand extends InfoCommand
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
        $this
            ->setName('doctrine:mapping:info')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command');
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
