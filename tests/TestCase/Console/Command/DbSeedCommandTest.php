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
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class DbSeedCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;

    protected $fixtures = ['Post'];
  
    public function testExecute()
    {
        $this->exec('db:seed --connection=test --type=sql');
        $this->assertExitSuccess();
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
        $this->assertOutputContains('Executed 3 statements');
    }

    public function testExecuteArgumentName()
    {
        $this->exec('db:seed --connection=test --type=sql seed');
        $this->assertExitSuccess();
        $this->assertOutputContains('Loading ' . ROOT . '/database/seed.sql');
    }
    
    public function testExecuteArgumentNameFileNotExists()
    {
        @mkdir(ROOT . 'plugins/make', 0775, true);
        Plugin::load('Make');
        $this->exec('db:seed --connection=test --type=sql Make.records');
        $this->assertExitError();
        $this->assertErrorContains('make/database/records.sql not found'); // check plugin name as well
    }
}
