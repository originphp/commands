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

    public function testExecute()
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE DATABASE dummy');

        $this->exec('db:drop --connection=dummy');
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `dummy` dropped');
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:drop --connection=foo');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found');
    }

    public function testExecuteDatabaseDoesNotExist()
    {
        $this->exec('db:drop --connection=dummy');
        $this->assertExitError();
        $this->assertOutputContains('Database `dummy` does not exist');
    }
}
