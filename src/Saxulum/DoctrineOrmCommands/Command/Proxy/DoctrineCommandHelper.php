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
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;

/**
 * Provides some helper and convenience methods to configure doctrine commands in the context of bundles
 * and multiple connections/entity managers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class DoctrineCommandHelper
{
    /**
     * Convenience method to push the helper sets of a given entity manager into the application.
     * @param  Application|null          $application
     * @param  string                    $emName
     * @throws \InvalidArgumentException
     */
    public static function setApplicationEntityManager(Application $application = null, $emName)
    {
        if (is_null($application)) {
            throw new \InvalidArgumentException('Application instance needed!');
        }

        $helperSet = $application->getHelperSet();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $helperSet->get('doctrine');

        /** @var EntityManager $em */
        $em = $doctrine->getManager($emName);

        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');
    }

    /**
     * Convenience method to push the helper sets of a given connection into the application.
     *
     * @param  Application               $application
     * @param  string                    $connName
     * @throws \InvalidArgumentException
     */
    public static function setApplicationConnection(Application $application = null, $connName)
    {
        if (is_null($application)) {
            throw new \InvalidArgumentException('Application instance needed!');
        }

        $helperSet = $application->getHelperSet();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $helperSet->get('doctrine');

        /** @var Connection $connection */
        $connection = $doctrine->getConnection($connName);

        $helperSet->set(new ConnectionHelper($connection), 'db');
    }
}
