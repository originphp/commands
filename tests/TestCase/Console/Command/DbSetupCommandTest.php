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

use Origin\Core\Plugin;
use Origin\Model\ConnectionManager;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbSetupCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp() : void
    {
        $config = ConnectionManager::config('test');
        $config['database'] = 'd4';
        ConnectionManager::config('d4', $config);
    }

    protected function tearDown() : void
    {
        $ds = ConnectionManager::get('d4');
        if ($this->isSqlite()) {
            @unlink('d4');
        } else {
            ConnectionManager::drop('d4'); // # PostgreIssues
            $ds = ConnectionManager::get('test');
            $ds->execute('DROP DATABASE IF EXISTS d4');
        }
    }
    
    public function testExecuteMySql()
    {
        if (ConnectionManager::get('test')->engine() !== 'mysql') {
            $this->markTestSkipped('This test is for MySQL');
        }
        
        $this->exec('db:setup --connection=d4 --type=sql');
        
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d4` created');
        $this->assertOutputContains('Loading '. ROOT . '/database/schema.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecutePostgres()
    {
        if (ConnectionManager::get('test')->engine() !== 'pgsql') {
            $this->markTestSkipped('This test is for PostgreSQL');
        }
        
        $this->exec('db:setup --connection=d4 --type=sql schema-pg');
      
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d4` created');
        $this->assertOutputContains('Loading '. ROOT . '/database/schema-pg.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecutePluginPath()
    {
        # Create fake plugin
        @mkdir(sys_get_temp_dir() . '/plugins/make', 0775, true);
        Plugin::load('Make', ['path'=>sys_get_temp_dir() . '/plugins/make']);

        $this->exec('db:setup --connection=d4 --type=sql Make.pschema');
        $this->assertExitError();

        $this->assertErrorContains('/plugins/make/database/pschema.sql');
    }

    /**
     * Load both schema and seed from php.
     *
     * @return void
     */
    public function testSetupPHP()
    {
        $this->exec('db:setup --connection=d4 --type=php');

        $this->assertExitSuccess();
        $expected = ConnectionManager::get('test')->engine() === 'pgsql' ?  9 : 4;

        $this->assertOutputContains('Loading '. ROOT . '/database/schema.php');
        $this->assertOutputContains('Executed '.$expected.' statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.php');
        $this->assertOutputContains('Executed 11 statements');
    }

    /**
     * @return boolean
     */
    private function isSqlite() : bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }
}
