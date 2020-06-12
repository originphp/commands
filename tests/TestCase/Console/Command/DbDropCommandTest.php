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
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbDropCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @return boolean
     */
    private function isSqlite() : bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }

    protected function setUp() : void
    {
        $config = ConnectionManager::config('test');
        $config['database'] = 'd2';
        ConnectionManager::config('d2', $config);
    }

    protected function tearDown() : void
    {
        ConnectionManager::drop('d2'); // # PostgreIssues
        $ds = ConnectionManager::get('test');
        if ($this->isSqlite()) {
            @unlink('d2');
        } else {
            $ds->execute('DROP DATABASE IF EXISTS d2');
        }
    }

    public function testExecute()
    {
        $ds = ConnectionManager::get('test');

        if ($this->isSqlite()) {
            file_put_contents('d2', 'foo');
        } else {
            $ds = ConnectionManager::get('test');
            $ds->execute('CREATE DATABASE d2');
        }

        $this->exec('db:drop --connection=d2');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d2` dropped');
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:drop --connection=foo');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found');
    }

    public function testExecuteDatabaseDoesNotExist()
    {
        $this->exec('db:drop --connection=d2');
        $this->assertExitError();
        $this->assertOutputContains('Database `d2` does not exist');
    }
}
