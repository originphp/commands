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
namespace Commands\Console\Command;

use Origin\Core\Config;
use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;
use Origin\Model\Engine\SqliteEngine;

class DbTestPrepareCommand extends Command
{
    protected $name = 'db:test:prepare';
    protected $description = 'Prepares the test database using the current schema file';
    
    protected function initialize(): void
    {
        /**
         * @deprecated Schema.format
         */
        $this->addOption('type', [
            'description' => 'Which schema type to be loaded sql or php',
            'default' => Config::read('App.schemaFormat') ?? Config::read('Schema.format'),
        ]);
    }
    protected function execute(): void
    {
        $config = ConnectionManager::config('test');
        if (! $config) {
            $this->throwError('test connection not found');
        }

        if ($this->databaseExists()) {
            $this->runCommand('db:drop', ['--connection=test']);
        }

        $this->runCommand('db:create', ['--connection=test']);
        $this->runCommand('db:schema:load', ['--connection' => 'test','--type' => $this->options('type')]);
    }

    private function databaseExists(): bool
    {
        $config = ConnectionManager::config('test');

        if ($this->isSQLite($config)) {
            return file_exists($config['database']);
        }

        // Create tmp Connection
        $database = $config['database'];
        $config['database'] = null;
        $connection = ConnectionManager::create('tmp', $config);

        return in_array($database, $connection->databases());
    }

    /**
     * @param array $config
     * @return boolean
     */
    private function isSQLite(array $config): bool
    {
        return (isset($config['engine']) && $config['engine'] === 'sqlite') || (isset($config['className']) && $config['className'] === SqliteEngine::class);
    }
}
