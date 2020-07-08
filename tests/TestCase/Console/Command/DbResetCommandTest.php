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
        $config['database'] = 'd3';
        ConnectionManager::config('d3', $config);
    }

    /**
    * @return boolean
    */
    private function isSqlite() : bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }

    protected function tearDown() : void
    {
        ConnectionManager::drop('d3'); // Postgres & SQLite issues
        if ($this->isSqlite()) {
            @unlink(ROOT . '/d3');
        } else {
            $ds = ConnectionManager::get('test');
            $ds->execute('DROP DATABASE IF EXISTS d3');
        }
    }

    public function testExecuteMySQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'mysql') {
            $this->markTestSkipped('This test is for mysql');
        }
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE d3;');
      
        $this->exec('db:reset --connection=d3 --type=sql');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d3` dropped');
        $this->assertOutputContains('Loading ' . ROOT . '/database/schema.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecutePostgreSQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'postgres') {
            $this->markTestSkipped('This test is for pgsql');
        }
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE d3;');
      
        $this->exec('db:reset --connection=d3 --type=sql schema-pg');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d3` dropped');
        $this->assertOutputContains('Loading ' . ROOT . '/database/schema-pg.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }
}
