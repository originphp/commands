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

/**
 * This is test is causing problems when run in group, have not identified the issue yet.
 */
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
        
        if ($this->isSqlite()) {
            @unlink(ROOT . '/tmp123');
            @unlink(ROOT . '/commands.sqlite3');
        } else {
            $connection = ConnectionManager::get('test');
            $connection->transaction(function ($connection) {
                foreach (['bookmarks', 'bookmarks_tags','tags','users'] as $table) {
                    $sql = $connection->adapter()->dropTableSql($table, ['ifExists' => true]);
                    $connection->execute($sql);
                }
            }, true);
        }
        ConnectionManager::drop('test');
        ConnectionManager::config('test', $this->config);
    }

    /**
    * @return boolean
    */
    private function isSqlite() : bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }
}
