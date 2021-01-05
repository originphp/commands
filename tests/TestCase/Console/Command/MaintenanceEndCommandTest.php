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
namespace Commands\Test\Console\Command;

use Origin\TestSuite\ConsoleIntegrationTestTrait;

class MaintenanceEndCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testNotDown()
    {
        @unlink(tmp_path('maintenance.json'));
   
        $this->exec('maintenance:end'); // Inject data
        $this->assertExitSuccess();
        $this->assertErrorContains('Application is not in maintainence mode.');
    }

    public function testEnd()
    {
        file_put_contents(
            tmp_path('maintenance.json'),
            '{"message":"Upgrading database","retry":60,"allowed":["192.168.1.100"],"time":1595079144}'
        );
   
        $this->exec('maintenance:end'); // Inject data

        $this->assertExitSuccess();
        $this->assertOutputContains('Application is no longer in maintainence mode.');

        @unlink(tmp_path('maintenance.json'));
    }
}
