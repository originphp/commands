<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright    Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license      https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace Commands\Test\Console\Command;

use Origin\Model\ConnectionManager;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbCreateCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp() : void
    {
        $config = ConnectionManager::config('test');
        $config['database'] = 'dummy';
        ConnectionManager::config('dummy', $config);
    }

    protected function tearDown() : void
    {
        ConnectionManager::drop('dummy'); // # PostgreIssues
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP DATABASE IF EXISTS dummy');
    }

    public function testExecuteMySQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'mysql') {
            $this->markTestSkipped('This test is for mysql');
        }
        $this->exec('db:create --connection=dummy');

        $this->assertExitSuccess();
        $this->assertOutputContains('Database `dummy` created');
    }

    public function testExecutePgSQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'pgsql') {
            $this->markTestSkipped('This test is for pgsql');
        }
        $this->exec('db:create --connection=dummy schema-pg');

        $this->assertExitSuccess();
        $this->assertOutputContains('Database `dummy` created');
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:create --connection=foo');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found');
    }

    public function testExecuteDatabaseAlreadyExists()
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE dummy');

        $this->exec('db:create --connection=dummy');
        $this->assertExitError();
        $this->assertOutputContains('Database `dummy` already exists');
    }

    public function testDatasourceException()
    {
        $config = ConnectionManager::config('test');
        $config['database'] = '<invalid-database-name>';
        ConnectionManager::config('dummy', $config);
        $this->exec('db:create --connection=dummy');
        $this->assertExitError();
        $this->assertErrorContains('DatasourceException');
    }

    public function shutdown() : void
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP DATABASE IF EXISTS dummy');
    }
}
