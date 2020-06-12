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

use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;
use Origin\Model\Engine\SqliteEngine;
use Origin\Model\Exception\DatasourceException;

class DbDropCommand extends Command
{
    protected $name = 'db:drop';
    protected $description = 'Drops the database for the connection';

    protected function initialize() : void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection',
            'short' => 'c',
            'default' => 'default',
        ]);
    }
 
    protected function execute() : void
    {
        $datasource = $this->options('connection');
        $config = ConnectionManager::config($datasource);
        if (! $config) {
            $this->throwError("{$datasource} connection not found");
        }
        if ((isset($config['engine']) && $config['engine'] === 'sqlite') || (isset($config['className']) && $config['className'] === SqliteEngine::class)) {
            $this->dropSqliteDatabase($config);
        } else {
            $this->dropDatabase($config);
        }
        
        $this->runCommand('cache:clear', ['--quiet']);
    }



    /**
     * Drops database for MySQL or Postgres engines
     *
     * @param array $config
     * @return void
     */
    private function dropDatabase(array $config) : void
    {
        // Create tmp Connection
        $database = $config['database'];
        $config['database'] = null;
        $connection = ConnectionManager::create('tmp', $config);

        if (! in_array($database, $connection->databases())) {
            $this->io->status('error', sprintf('Database `%s` does not exist', $database));
            $this->abort();
        }
        try {
            $connection->execute("DROP DATABASE {$database}");
            ConnectionManager::drop('tmp');
            $this->io->status('ok', sprintf('Database `%s` dropped', $database));
        } catch (DatasourceException $ex) {
            $this->throwError('DatasourceException', $ex->getMessage());
        }
    }

    /**
     * @param array $config
     * @return void
     */
    private function dropSqliteDatabase(array $config) : void
    {
        if (!file_exists($config['database'])) {
            $this->io->status('error', sprintf('Database `%s` does not exist', $config['database']));
            $this->abort();
        }

        if (unlink($config['database'])) {
            $this->io->status('ok', sprintf('Database `%s` dropped', $config['database']));
        } else {
            $this->throwError('DatasourceException', 'Unable to drop the database.');
        }
    }
}
