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

use Origin\Model\ConnectionManager;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbRollbackCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;

    protected $fixtures = ['Migration'];

    public function testRollback()
    {
        // Load the Migrations from file
        $this->exec('db:migrate --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('Migration Complete. 3 migrations in 0 ms');

        $this->exec('db:rollback --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('Rollback Complete. 1 migrations in 0 ms');

        $this->exec('db:rollback --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('Rollback Complete. 1 migrations in 0 ms');

        $this->exec('db:rollback --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('Rollback Complete. 1 migrations in 0 ms');
    }

    public function testNoMigrations()
    {
        $this->exec('db:rollback --connection=test');
        $this->assertExitSuccess();
        $this->assertErrorContains('No migrations found'); // Its a warning
    }

    protected function tearDown(): void
    {
        $ds = ConnectionManager::get('test');
        $ds->execute('DROP table IF EXISTS foo');
        $ds->execute('DROP table IF EXISTS bar');
        $ds->execute('DROP table IF EXISTS foobar');
    }
}
