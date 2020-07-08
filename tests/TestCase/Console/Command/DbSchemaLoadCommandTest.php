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
use Origin\Core\Plugin;

class DbSchemaLoadCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp() : void
    {
        // Create copy
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP TABLE IF EXISTS authors');
        $ds->execute('DROP TABLE IF EXISTS posts');
    }
    protected function getSchemaName()
    {
        $engine = ConnectionManager::get('test')->engine();
        if ($engine === 'postgres') {
            return 'schema-pg';
        }

        return 'schema';
    }
    public function testExecute()
    {
        $name = $this->getSchemaName();

        $this->exec('db:schema:load --connection=test --type=sql ' . $this->getSchemaName());
   
        $this->assertExitSuccess();
        $this->assertOutputContains('Executed 2 statements');
    }

    public function testExecuteInvalidSQL()
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE TABLE authors (id INT)');
   
        $this->exec('db:schema:load --connection=test --type=sql '. $this->getSchemaName());
    
        $this->assertExitError();
        $this->assertErrorContains('Executing query failed'); # Using normal output for this
    }

    public function testExecuteInvalidSchemaFile()
    {
        $this->exec('db:schema:load --connection=test --type=sql dummy');
        $this->assertExitError();
        $this->assertErrorContains('File ' . ROOT . '/database/dummy.sql not found'); # Using normal output for this
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:schema:load --connection=foo --type=sql');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found'); # Using normal output for this
    }

    /**
     * Test using Plugin.schema
     *
     * @return void
     */
    public function testExecutePluginSchemaFile()
    {
        # Create fake plugin
        @mkdir(sys_get_temp_dir() . '/plugins/make', 0775, true);
        Plugin::load('Make', ['path'=>sys_get_temp_dir() . '/plugins/make']);
        $this->exec('db:schema:load --connection=test --type=sql Make.pschema');
        $this->assertExitError();
        $this->assertErrorContains('/plugins/make/database/pschema.sql');
    }

    public function testExecuteLoadPHPSchema()
    {
        $this->exec('db:schema:load --connection=test --type=php migrations');
        $this->assertExitSuccess();

        $this->assertMatchesRegularExpression('/Executed (1|2) statements/', $this->output());
        ConnectionManager::get('test')->execute('DROP TABLE IF EXISTS migrations');
    }

    public function testLoadUnkownType()
    {
        $this->exec('db:schema:load --connection=test --type=ruby');
        $this->assertExitError();
        $this->assertErrorContains('The type `ruby` is invalid');
    }

    protected function tearDown() : void
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP TABLE IF EXISTS posts');
    }
}
