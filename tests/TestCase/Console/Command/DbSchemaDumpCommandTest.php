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

class DbSchemaDumpCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;
    protected $fixtures = ['Post'];
 
    public function testDumpingFile()
    {
        $filename = DATABASE . DS . 'dump.sql';

        $database = ConnectionManager::config('test')['database'];

        $this->exec('db:schema:dump --connection=test --type=sql dump');
        $this->assertExitSuccess();
        $this->assertOutputContains('Dumping database `'.$database.'` schema to ' . DATABASE . DS . 'dump.sql');
        $this->assertTrue(file_exists($filename));

        $this->assertOutputContains('* posts');

        $contents = file_get_contents($filename);
        // Different versions of MySQL also return different results, so test sample
      
        if (ConnectionManager::get('test')->engine() === 'mysql') {
            $this->assertStringContainsString('CREATE TABLE `posts` (', $contents);
            $this->assertStringContainsString('`title` varchar(255) NOT NULL,', $contents);
        } else { //pgsql or sqlite
            $this->assertStringContainsString('CREATE TABLE "posts" (', $contents);
            $this->assertStringContainsString('"title" VARCHAR(255) NOT NULL,', $contents);
        }
    }

    public function testDumpPHP()
    {
        $filename = DATABASE . DS . 'dump.php';
        $this->exec('db:schema:dump --connection=test --type=php dump');

        $database = ConnectionManager::config('test')['database'];

        // use ds for windows based testing
        $this->assertExitSuccess();
        $this->assertOutputContains('Dumping database `'.$database.'` schema to ' . DATABASE . DS . 'dump.php');
        $this->assertTrue(file_exists($filename));
        $this->assertOutputContains('* posts');

        // Check is valid object and some spot checks
        include $filename;
        $schema = new \DumpSchema();
        $this->assertInstanceOf(\DumpSchema::class, $schema);
        $this->assertNotEmpty($schema->posts);
        $this->assertEquals('integer', $schema->posts['columns']['id']['type']);
        $this->assertNotEmpty($schema->posts['constraints']);
        $this->assertNotEmpty($schema->posts['constraints']['primary']);
    }

    public function testDumpSqlException()
    {
        $this->exec('db:schema:dump --connection=test --type=sql dump', ['n']);
        $this->assertExitError();
        $this->assertErrorContains('Error saving schema file');
        @unlink(DATABASE . DS . '/dump.sql');
    }

    public function testDumpPHPException()
    {
        $this->exec('db:schema:dump --connection=test --type=php dump', ['n']);
        $this->assertExitError();
        $this->assertErrorContains('Error saving schema file');
        @unlink(DATABASE . DS . '/dump.php');
    }

    public function testDumpUnkownType()
    {
        $this->exec('db:schema:dump --connection=test --type=ruby');
        $this->assertExitError();
        $this->assertErrorContains('The type `ruby` is invalid');
    }

    public function testExecuteInvalidDatasource()
    {
        $this->exec('db:schema:dump --connection=foo');
        $this->assertExitError();
        $this->assertErrorContains('foo connection not found');
    }
}
