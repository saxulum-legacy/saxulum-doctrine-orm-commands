<?php

namespace Saxulum\Tests\DoctrineOrmCommands\Command;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Dominikzogg\Silex\Provider\DoctrineOrmManagerRegistryProvider;
use Saxulum\Console\Silex\Provider\ConsoleProvider;
use Saxulum\DoctrineOrmCommands\Command\CreateDatabaseDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\DropDatabaseDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\GenerateEntitiesDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearMetadataCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearQueryCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearResultCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ConvertMappingDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\CreateSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\DropSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\EnsureProductionSettingsDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\InfoDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\RunDqlDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\RunSqlDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\UpdateSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ValidateSchemaCommand;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandTest extends WebTestCase
{
    public function testCommands()
    {
        $output = new BufferedOutput();

        $this->app['console']->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--complete' => true
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:query:dql',
            'dql' => 'SELECT e FROM Saxulum\Tests\DoctrineOrmCommands\Entity\Example e',
            '--hydrate' => 'array'
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:query:sql',
            'sql' => 'SELECT * FROM example',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:mapping:convert',
            'to-type' => 'xml',
            'dest-path' => $this->getTestDirectoryPath(),
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $xmlPath = $this->getTestDirectoryPath() . '/Saxulum.Tests.DoctrineOrmCommands.Entity.Example.orm.xml';
        $this->assertFileExists($xmlPath);
        unlink($xmlPath);

        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-metadata',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-query',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-result',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:mapping:info',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:validate',
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:ensure-production-setting',
            '--complete' => true,
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:drop',
            '--force' => true
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function createApplication()
    {
        $app = new Application();

        $app->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_sqlite',
                'path'     => $this->getTestDirectoryPath() .'/sqlite.db',
            ),
        ));
        $app->register(new DoctrineOrmServiceProvider(), array(
            'orm.em.options' => array(
                'mappings' => array(
                    array(
                        'type' => 'annotation',
                        'namespace' => 'Saxulum\Tests\DoctrineOrmCommands\Entity',
                        'path' => $this->getTestDirectoryPath() .'/Saxulum/Tests/DoctrineOrmCommands/Entity',
                        'use_simple_annotation_reader' => false,
                    ),
                ),
            ),
        ));

        $app->register(new DoctrineOrmManagerRegistryProvider());
        $app->register(new ConsoleProvider());

        $app['console.commands'] = $app->share(
            $app->extend('console.commands', function ($commands) use ($app) {
                $commands[] = new CreateDatabaseDoctrineCommand(null, $app['doctrine']);
                $commands[] = new DropDatabaseDoctrineCommand(null, $app['doctrine']);
                $commands[] = new CreateSchemaDoctrineCommand(null, $app['doctrine']);
                $commands[] = new UpdateSchemaDoctrineCommand(null, $app['doctrine']);
                $commands[] = new DropSchemaDoctrineCommand(null, $app['doctrine']);
                $commands[] = new RunDqlDoctrineCommand(null, $app['doctrine']);
                $commands[] = new RunSqlDoctrineCommand(null, $app['doctrine']);
                $commands[] = new ConvertMappingDoctrineCommand(null, $app['doctrine']);
                $commands[] = new ClearMetadataCacheDoctrineCommand(null, $app['doctrine']);
                $commands[] = new ClearQueryCacheDoctrineCommand(null, $app['doctrine']);
                $commands[] = new ClearResultCacheDoctrineCommand(null, $app['doctrine']);
                $commands[] = new InfoDoctrineCommand(null, $app['doctrine']);
                $commands[] = new ValidateSchemaCommand(null, $app['doctrine']);
                $commands[] = new EnsureProductionSettingsDoctrineCommand(null, $app['doctrine']);

                return $commands;
            })
        );

        $app->boot();

        return $app;
    }

    protected function getTestDirectoryPath()
    {
        return realpath(__DIR__.'/../../../..');
    }
}