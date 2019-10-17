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

class DbResetCommandTest extends \PHPUnit\Framework\TestCase
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
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE dummy;');
      
        $this->exec('db:reset --connection=dummy --type=sql');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `dummy` dropped');
        $this->assertOutputContains('Loading ' . ROOT . '/database/schema.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecutePostgreSQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'pgsql') {
            $this->markTestSkipped('This test is for pgsql');
        }
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE dummy;');
      
        $this->exec('db:reset --connection=dummy --type=sql schema-pg');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `dummy` dropped');
        $this->assertOutputContains('Loading ' . ROOT . '/database/schema-pg.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }
}
