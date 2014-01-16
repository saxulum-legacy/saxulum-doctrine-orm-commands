<?php

namespace Saxulum\Tests\DoctrineOrmCommands\Command;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Dominikzogg\Silex\Provider\DoctrineOrmManagerRegistryProvider;
use Saxulum\Console\Silex\Provider\ConsoleProvider;
use Saxulum\DoctrineOrmCommands\Command\CreateDatabaseDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\DropDatabaseDoctrineCommand;
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
use Saxulum\DoctrineOrmCommands\Helper\ManagerRegistryHelper;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandTest extends WebTestCase
{
    public function testDatabaseCreateCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testSchemaCreateCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testSchemaUpdateCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--complete' => true
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testQueryDqlCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:query:dql',
            'dql' => 'SELECT e FROM Saxulum\Tests\DoctrineOrmCommands\Entity\Example e',
            '--hydrate' => 'array'
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testQuerySqlCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:query:sql',
            'sql' => 'SELECT * FROM example',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testMappingConvertCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:mapping:convert',
            'to-type' => 'xml',
            'dest-path' => $this->getTestDirectoryPath(),
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());

        $xmlPath = $this->getTestDirectoryPath() . '/Saxulum.Tests.DoctrineOrmCommands.Entity.Example.orm.xml';
        $this->assertFileExists($xmlPath);
        unlink($xmlPath);
    }

    public function testCacheClearMetadataCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-metadata',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testCacheClearQueryCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-query',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testCacheClearResultCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:cache:clear-result',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testMappingInfoCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:mapping:info',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testSchemaValidateCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:validate',
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testEnsureProductionSettingCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:ensure-production-setting',
            '--complete' => true,
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testSchemaDropCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:drop',
            '--force' => true
        ));
        $output = new BufferedOutput();
        $this->app['console']->run($input, $output);
        echo($output->fetch());
    }

    public function testDatabaseDropCommand()
    {
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $output = new BufferedOutput();
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

        $app['console'] = $app->share(
            $app->extend('console', function (ConsoleApplication $consoleApplication) use ($app) {
                $consoleApplication->setAutoExit(false);
                $helperSet = $consoleApplication->getHelperSet();
                $helperSet->set(new ManagerRegistryHelper($app['doctrine']), 'doctrine');

                return $consoleApplication;
            })
        );

        $app['console.commands'] = $app->share(
            $app->extend('console.commands', function ($commands) use ($app) {
                $commands[] = new CreateDatabaseDoctrineCommand;
                $commands[] = new DropDatabaseDoctrineCommand;
                $commands[] = new CreateSchemaDoctrineCommand;
                $commands[] = new UpdateSchemaDoctrineCommand;
                $commands[] = new DropSchemaDoctrineCommand;
                $commands[] = new RunDqlDoctrineCommand;
                $commands[] = new RunSqlDoctrineCommand;
                $commands[] = new ConvertMappingDoctrineCommand;
                $commands[] = new ClearMetadataCacheDoctrineCommand;
                $commands[] = new ClearQueryCacheDoctrineCommand;
                $commands[] = new ClearResultCacheDoctrineCommand;
                $commands[] = new InfoDoctrineCommand;
                $commands[] = new ValidateSchemaCommand;
                $commands[] = new EnsureProductionSettingsDoctrineCommand;

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
