<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright     Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);
namespace Commands\Console\Command;

use Exception;
use Origin\Console\Command\Command;

class MaintenanceEndCommand extends Command
{
    protected $name = 'maintenance:end';
    protected $description = 'Takes your application out of maintenance mode';

    protected function execute(): void
    {
        if (! file_exists(tmp_path('maintenance.json'))) {
            $this->warning('Application is not in maintainence mode.');
            $this->exit();
        }

        try {
            unlink(tmp_path('maintenance.json'));
            $this->success('Application is no longer in maintainence mode.');
        } catch (Exception $exception) {
            $this->error('Application could not be taken out of maintaince mode.');
            $this->debug($exception->getMessage());
        }
    }
}
