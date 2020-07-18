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

class MaintenanceStartCommand extends Command
{
    protected $name = 'maintenance:start';
    protected $description = 'Puts your application in maintenance mode.';
 
    protected function initialize(): void
    {
        $this->addOption('message', [
            'description' => 'Custom message to be shown',
            'default' => 'The site is temporarily down for maintenance.'
        ]);

        /**
         * Set Retry-After: HTTP header
         */
        $this->addOption('retry', [
            'description' => 'The number of seconds after which the request is ',
            'default' => 0,
            'type' => 'integer'
        ]);

        $this->addOption('allow', [
            'description' => 'IP address to be allowed',
            'type' => 'array',
        ]);
    }

    protected function execute(): void
    {
        if (file_exists(tmp_path('maintenance.json'))) {
            $this->warning('Application is already in maintainence mode.');
            $this->exit();
        }

        try {
            file_put_contents(
                tmp_path('maintenance.json'),
                json_encode($this->payload(), JSON_PRETTY_PRINT)
            );
            $this->success('Application is now in maintainence mode.');
        } catch (Exception $exception) {
            $this->error('Application could not be placed in maintaince mode.');
            $this->debug($exception->getMessage());
        }
    }

    /**
     * @return array
     */
    private function payload(): array
    {
        return [
            'message' => $this->options('message'),
            'retry' => $this->options('retry'),
            'allowed' => $this->options('allow'),
            'time' => time()
        ];
    }
}
