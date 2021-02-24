<?php
declare(strict_types = 1);
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
