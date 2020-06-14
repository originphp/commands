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

use Exception;
use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;
use Origin\Model\Engine\SqliteEngine;
use Origin\Model\Exception\ConnectionException;
use Origin\Model\Exception\DatasourceException;

class DbCreateCommand extends Command
{
    protected $name = 'db:create';
    protected $description = 'Creates the database for the connection';
    
    protected function initialize() : void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection','short' => 'c','default' => 'default',
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
            $this->createSqliteDatabase($config);
        } else {
            $this->createDatabase($config);
        }
    }

    /**
     * Creates database for MySQL or Postgres engines
     *
     * @param array $config
     * @return void
     */
    private function createDatabase(array $config) : void
    {
        $database = $config['database'];
        $config['database'] = null;
        $connection = ConnectionManager::create('tmp', $config); // add without database so we can connect
        if (in_array($database, $connection->databases())) {
            $this->io->status('error', sprintf('Database `%s` already exists', $database));
            $this->abort();
        }
        try {
            $connection->execute("CREATE DATABASE {$database}");
            // Bug Cannot execute queries while other unbuffered queries are active , despite no results can be fetched.
            ConnectionManager::drop('tmp');
            $this->io->status('ok', sprintf('Database `%s` created', $database));
        } catch (DatasourceException $ex) {
            $this->throwError('DatasourceException', $ex->getMessage());
        }
    }

    /**
     * @param array $config
     * @return void
     */
    private function createSqliteDatabase(array $config) : void
    {
        $database = str_replace(ROOT . '/', '', $config['database']);

        if (file_exists($config['database'])) {
            $this->io->status('error', sprintf('Database `%s` already exists', $database));
            $this->abort();
        }

        $error = sprintf('Database `%s` not created.', $database);
   
        try {
            ConnectionManager::get($this->options('connection')); // create the db by connecting to it
            $this->io->status('ok', sprintf('Database `%s` created', $database));
            return;
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }
      
        
        $this->throwError('DatasourceException', $error);
    }
}
