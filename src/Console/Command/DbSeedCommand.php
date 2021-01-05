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
namespace Commands\Console\Command;

use Origin\Core\Config;
use Origin\Model\Connection;
use Origin\Inflector\Inflector;
use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;
use Origin\Model\Exception\DatasourceException;

class DbSeedCommand extends Command
{
    use DbSchemaTrait,DeprecationNoticeTrait;
    protected $name = 'db:seed';

    protected $description = 'Seeds the database with initial records';

    protected function initialize(): void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection',
            'short' => 'c',
            'default' => 'default',
        ]);
        $this->addArgument('name', [
            'description' => 'seed or Plugin.seed',
        ]);
        /**
         * @deprecated Schema.format
         */
        $this->addOption('type', [
            'description' => 'Wether to use sql or php',
            'default' => Config::read('App.schemaFormat')
        ]);
        $this->checkForDeprecations();
    }
 
    protected function execute(): void
    {
        $name = $this->arguments('name') ?? 'seed';
          
        $datasource = $this->options('connection');
        $type = $this->options('type');
        $filename = $this->schemaFilename($name, $type);
        
        if ($type === 'php') {
            $this->loadPHPSeed($name, $filename, $datasource);
        } else {
            $this->loadSchema($filename, $datasource);
        }
    }

    protected function loadPHPSeed(string $name, string $filename, string $datasource): void
    {
        if (! file_exists($filename)) {
            $this->throwError("File {$filename} not found");
        }
        $this->io->info("Loading {$filename}");
       
        if (! ConnectionManager::config($datasource)) {
            $this->throwError("{$datasource} connection not found");
        }
        $connection = ConnectionManager::get($datasource);

        list($plugin, $name) = pluginSplit($name);
        $class = 'ApplicationSeed';
        if ($name !== 'seed') {
            $class = Inflector::studlyCaps($name) . 'Seed';
        }
       
        include_once $filename;
        $seed = new $class;
      
        $statements = $seed->insertSql($connection);
        $count = $this->executePreparedStatements($statements, $connection);
   
        $this->io->success(sprintf('Executed %d statements', $count));
    }

    /**
    * Runs a set of statments against a datasource
    *
    * @param array $statements
    * @param \Origin\Model\Connection $connection
    * @return integer
    */
    protected function executePreparedStatements(array $statements, Connection $connection): int
    {
        $connection->transaction(function ($connection) use ($statements) {
            $this->processPreparedStatements($connection, $statements);
        }, true);
        
        return count($statements);
    }
    
    /**
     * @param \Origin\Model\Connection $connection
     * @param array $statements
     * @return void
     */
    protected function processPreparedStatements(Connection $connection, array $statements): void
    {
        foreach ($statements as $statement) {
            $this->processPreparedStatement($connection, $statement);
        }
    }
    
    /**
     * @param \Origin\Model\Connection $connection
     * @param array $statements
     * @return void
     */
    protected function processPreparedStatement(Connection $connection, array $statement): void
    {
        try {
            $sql = $this->unprepare($statement[0], $statement[1]);
            $connection->execute($statement[0], $statement[1]);

            $this->io->status('ok', $sql);
        } catch (DatasourceException $ex) {
            $this->io->status('error', $sql);
            $this->throwError('Executing query failed', $ex->getMessage());
        }
    }

    /**
     * This has been taken from Datasource:unprepare.
     *
     * @param string $sql
     * @param array $params
     * @return string
     */
    protected function unprepare(string $sql, array  $params): string
    {
        foreach ($params as $needle => $replace) {
            if (is_string($replace)) {
                $replace = "'{$replace}'";
            }
            $sql = preg_replace("/\B:{$needle}/", $replace, $sql);
        }

        return $sql;
    }
}
