<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
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

use Origin\TestSuite\ConsoleIntegrationTestTrait;

class MaintenanceStartCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testExecution()
    {
        @unlink(tmp_path('maintenance.json'));
   
        $this->exec('maintenance:start --message="Upgrading database" --retry=60 --allow=192.168.1.100'); // Inject data

        $this->assertExitSuccess();
        $this->assertOutputContains('Application is now in maintainence mode.');
        $payload = file_get_contents(tmp_path('maintenance.json'));
        $actual = json_decode($payload, true);
        $this->assertEquals('Upgrading database', $actual['message']);
        $this->assertEquals(60, $actual['retry']);
        $this->assertEquals(['192.168.1.100'], $actual['allowed']);
    }

    public function testAlreadyInMaintenceMode()
    {
        $this->exec('maintenance:start');
        $this->assertExitSuccess();
        $this->assertErrorContains('Application is already in maintainence mode.');
    }
}
