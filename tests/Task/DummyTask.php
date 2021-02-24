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
declare(strict_types=1);
namespace Commands\Test\Task;

use Origin\Schedule\Schedule;
use Origin\Schedule\Task;

class DummyTask extends Task
{
    protected function handle(Schedule $schedule): void
    {
        # 3a1787289e29
        $event = $schedule->call(function () {
            return true;
        })->everyMinute();
    }
}
