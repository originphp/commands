<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
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

    /**
     * @return boolean
     */
    private function isSqlite(): bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }

    protected function setUp(): void
    {
        $config = ConnectionManager::config('test');
        $config['database'] = 'd1';
        ConnectionManager::config('d1', $config);
    }

    protected function tearDown(): void
    {
        ConnectionManager::drop('d1'); // Postgres & SQLite issues
        if ($this->isSqlite()) {
            @unlink(ROOT . '/d1');
        } else {
            $ds = ConnectionManager::get('test');
            $ds->execute('DROP DATABASE IF EXISTS d1');
        }
    }

    public function testExecuteMySQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'mysql') {
            $this->markTestSkipped('This test is for mysql');
        }
        $this->exec('db:create --connection=d1');

        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d1` created');
    }

    public function testExecutePgSQL()
    {
        if (ConnectionManager::get('test')->engine() !== 'postgres') {
            $this->markTestSkipped('This test is for pgsql');
        }
        $this->exec('db:create --connection=d1 schema-pg');

        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d1` created');
    }

    public function testExecutSqlite()
    {
        if (ConnectionManager::get('test')->engine() !== 'sqlite') {
            $this->markTestSkipped('This test is for sqlite');
        }
        $this->exec('db:create --connection=d1');

        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d1` created');
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:create --connection=foo');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found');
    }

    public function testExecuteDatabaseAlreadyExists()
    {
        if ($this->isSqlite()) {
            file_put_contents('d1', 'foo');
        } else {
            $ds = ConnectionManager::get('test');
            $ds->execute('CREATE DATABASE d1');
        }
    
        $this->exec('db:create --connection=d1');
        $this->assertExitError();
        $this->assertOutputContains('Database `d1` already exists');
    }

    public function testDatasourceException()
    {
        $config = ConnectionManager::config('test');
        if ($this->isSqlite()) {
            $config['database'] = '/somewhere/that/does/not/exist/database.md';
        } else {
            $config['database'] = '<invalid-database-name>';
        }
       
        ConnectionManager::config('d1', $config);
        $this->exec('db:create --connection=d1');
        $this->assertExitError();
        $this->assertErrorContains('DatasourceException');
    }

    public function shutdown(): void
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP DATABASE IF EXISTS d1');
    }
}
