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

class DbTestPrepareCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    protected $config = [];
    protected function setUp() : void
    {
        $config = $this->config = ConnectionManager::config('test');
        $config['database'] = 'tmp123';
        ConnectionManager::config('test', $config);
    }
   
    public function testExecute()
    {
        $this->exec('db:test:prepare --type=php');
        $this->assertExitSuccess();
        $this->assertRegExp('/Executed ([1-9]) statements/', $this->output());
    }

    protected function tearDown() : void
    {
        /**
         * Clean up tables
         */
        
        $connection = ConnectionManager::get('test');
        $connection->disableForeignKeyConstraints();
        foreach (['bookmarks', 'bookmarks_tags','tags','users'] as $table) {
            $sql = $connection->adapter()->dropTableSql($table, ['ifExists' => true]);
            $connection->execute($sql);
        }
        $connection->enableForeignKeyConstraints();
        ConnectionManager::drop('test');
        ConnectionManager::config('test', $this->config);
    }
}
