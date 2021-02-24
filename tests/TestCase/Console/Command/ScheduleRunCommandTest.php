<?php

use Origin\TestSuite\ConsoleIntegrationTestTrait;

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


class ScheduleRunCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    private function taskPath()
    {
        return dirname(__DIR__, 3) . '/Task';
    }

   

    public function testInvalidDirectory()
    {
        $this->exec('schedule:run');
        $this->assertExitError();
        $this->assertErrorContains('Directory does not exist');
    }

    public function testWithDirectory()
    {
        $this->exec('schedule:run --directory=' . $this->taskPath());
        $this->assertExitSuccess();
    }

    public function testWithDirectoryAndID()
    {
        $this->exec('schedule:run --id=3a1787289e29 --directory=' . $this->taskPath());
        $this->assertExitSuccess();
    }

    public function testWithDirectoryAndInvalidID()
    {
        $this->exec('schedule:run --id=1234 --directory=' . $this->taskPath());
        $this->assertExitError();
        $this->assertErrorContains('Invalid event ID');
    }
}
