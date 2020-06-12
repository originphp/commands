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

use Origin\Model\Concern\Timestampable;
use Origin\Model\ConnectionManager;
use Origin\Model\Model;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class Migration extends Model
{
    use Timestampable;
}

class DbMigrateCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;

    protected $fixtures = ['Migration'];


    protected function setUp() : void
    {
        $this->Migration = $this->loadModel('Migration', [
            'className' => Migration::class,
            'connection' => 'test'
        ]);
    }

    protected function tearDown() : void
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP table IF EXISTS foo');
        $ds->execute('DROP table IF EXISTS bar');
        $ds->execute('DROP table IF EXISTS foobar');
    }

    public function testMigrate()
    {
        $this->exec('db:migrate --connection=test');
       
        $migrations = $this->Migration->find('all');
     
        $this->assertStringContainsString('["DROP TABLE', $migrations[0]['rollback']);
        $this->assertStringContainsString('["DROP TABLE', $migrations[1]['rollback']);
        $this->assertStringContainsString('["DROP TABLE', $migrations[2]['rollback']);

        $this->assertExitSuccess();
        $this->assertOutputContains('Migration Complete. 3 migrations in 0 ms');
    }

    /**
     * @depends testMigrate
     */
    public function testRollback()
    {
        $this->exec('db:migrate --connection=test'); // Inject data

        $this->exec('db:migrate --connection=test 20190520033225');
        $this->assertExitSuccess();
        $this->assertOutputContains('Rollback Complete. 3 migrations in 0 ms');
    }

    public function testNoMigrations()
    {
        $this->exec('db:migrate --connection=test'); // Run Migrations
        $this->exec('db:migrate --connection=test'); // Run Again (this time none)
        $this->assertExitSuccess();
        $this->assertErrorContains('No migrations found'); // Its a warning
    }

    public function testNoMigrationsRollback()
    {
        $this->exec('db:migrate --connection=test'); // Inject data
        $this->exec('db:migrate --connection=test 20190520033226'); // Rollback
        $this->exec('db:migrate --connection=test 20190520033226'); // Now there should be no migrations
        $this->assertExitSuccess();
        $this->assertErrorContains('No migrations found');
    }

    public function testMigrateException()
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('CREATE TABLE foo (id INT)');
        $this->exec('db:migrate --connection=test'); // Inject data
        $this->assertExitError();
    }

    public function testMigrateRollbackException()
    {
        $this->exec('db:migrate --connection=test'); // Inject data

        $ds = ConnectionManager::get('test');
        $ds->execute('DROP TABLE foo');

        $this->exec('db:migrate --connection=test 20190520033225'); // Rollback

        $this->assertExitError();
    }
}
