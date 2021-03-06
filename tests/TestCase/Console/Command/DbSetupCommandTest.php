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
namespace Commands\Test\TestCase\Console\Command;

use Origin\Core\Plugin;
use Origin\Model\ConnectionManager;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbSetupCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp(): void
    {
        $config = ConnectionManager::config('test');
        $config['database'] = 'd4';
        
        ConnectionManager::config('d4', $config);
    }

    protected function tearDown(): void
    {
        ConnectionManager::drop('d4'); // Postgres & sqlite issues
        if ($this->isSqlite()) {
            if (file_exists(ROOT . '/d4')) {
                unlink(ROOT . '/d4');
            }
        } else {
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
        if (ConnectionManager::get('test')->engine() !== 'postgres') {
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

    public function testExecuteSqlite()
    {
        if (ConnectionManager::get('test')->engine() !== 'sqlite') {
            $this->markTestSkipped('This test is for SQLite');
        }
        
        $this->exec('db:setup --connection=d4 --type=sql');
        
        $this->assertExitSuccess();
        $this->assertOutputContains('Database `d4` created');
        $this->assertOutputContains('Loading '. ROOT . '/database/schema.sql');
        $this->assertOutputContains('Executed 2 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecutePluginPath()
    {
        # Create fake plugin
        $directory = sys_get_temp_dir() . '/plugins/make';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    
        Plugin::load('Make', ['path' => sys_get_temp_dir() . '/plugins/make']);

        $this->exec('db:setup --connection=d4 --type=sql Make.pschema');

        $this->assertExitError();

        $this->assertErrorContains('/plugins/make/database/pschema.sql');
    }

    /**
     * Load both schema and seed from php.
     *
     * @return void
     */
    public function testSetupPHPSqlite()
    {
        if (ConnectionManager::get('test')->engine() !== 'sqlite') {
            $this->markTestSkipped('This test is for SQLite');
        }

        $this->exec('db:setup --connection=d4 --type=php');
       
        $this->assertExitSuccess();
  
        $this->assertOutputContains('Loading '. ROOT . '/database/schema.php');
        $this->assertOutputContains('Executed 6 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.php');
        $this->assertOutputContains('Executed 11 statements');
    }

    /**
     * Load both schema and seed from php.
     *
     * @return void
     */
    public function testSetupPHPPgsql()
    {
        if (ConnectionManager::get('test')->engine() !== 'postgres') {
            $this->markTestSkipped('This test is for Postgres');
        }
        $this->exec('db:setup --connection=d4 --type=php');
       
        $this->assertExitSuccess();

        $this->assertOutputContains('Loading '. ROOT . '/database/schema.php');
        $this->assertOutputContains('Executed 9 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.php');
        $this->assertOutputContains('Executed 11 statements');
    }

    /**
     * Load both schema and seed from php.
     *
     * @return void
     */
    public function testSetupPHPMysql()
    {
        if (ConnectionManager::get('test')->engine() !== 'mysql') {
            $this->markTestSkipped('This test is for Mysql');
        }
        $this->exec('db:setup --connection=d4 --type=php');
       
        $this->assertExitSuccess();

        $this->assertOutputContains('Loading '. ROOT . '/database/schema.php');
        $this->assertOutputContains('Executed 4 statements');
        $this->assertOutputContains('Loading '. ROOT . '/database/seed.php');
        $this->assertOutputContains('Executed 11 statements');
    }

    /**
     * @return boolean
     */
    private function isSqlite(): bool
    {
        return ConnectionManager::get('test')->engine() === 'sqlite';
    }
}
