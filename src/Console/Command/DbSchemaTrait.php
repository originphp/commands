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

use Origin\Core\Plugin;
use Origin\Model\Connection;
use Origin\Model\ConnectionManager;
use Origin\Model\Exception\DatasourceException;

trait DbSchemaTrait
{
    
    /**
     * Gets the filename for the schema
     *
     * @param string $name schema or Plugin.schema
     * @return string
     */
    public function schemaFilename(string $name, string $extension = 'sql'): string
    {
        list($plugin, $file) = pluginSplit($name);
        if ($plugin) {
            return Plugin::path($plugin) . DS . 'database' . DS .  $file . '.' . $extension;
        }

        return DATABASE . DS . $file . '.' . $extension;
    }

    /**
     * Parses a SQL string into an array of statements
     *
     * @param string $sql
     * @return array
     */
    public function parseSql(string $sql)
    {
        # Clean Up Soure Code
        $sql = str_replace(";\r\n", ";\n", $sql); // Convert windows line endings on STATEMENTS ONLY
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);
        $sql = preg_replace('/^-- .*$/m', '', $sql); // Remove Comment line starting with --
        $sql = preg_replace('/^#.*$/m', '', $sql); // Remove Comments start with #
  
        $statements = [];
        if ($sql) {
            $statements = explode(";\n", $sql);
            $statements = array_map('trim', $statements);
        }
      
        return $statements;
    }

    /**
     * Runs the contents of a sql schema file
     *
     * @param string $filename
     * @param string $datasource
     * @return void
     */
    public function loadSchema(string $filename, string $datasource): void
    {
        if (! file_exists($filename)) {
            $this->throwError("File {$filename} not found");
        }
        $this->io->info("Loading {$filename}");
       
        if (! ConnectionManager::config($datasource)) {
            $this->throwError("{$datasource} connection not found");
        }
        $connection = ConnectionManager::get($datasource);
        
        $statement = file_get_contents($filename);
        $statements = $this->parseSql($statement);

        $count = $this->executeStatements($statements, $connection);

        $this->io->success(sprintf('Executed %d statements', $count));
    }

    /**
    * Runs a set of statments against a datasource
    *
    * @param array $statements
    * @param \Origin\Model\Connection $connection
    * @return integer
    */
    protected function executeStatements(array $statements, Connection $connection): int
    {
        $connection->transaction(function ($connection) use ($statements) {
            $this->processStatements($connection, $statements);
        }, true);

        return count($statements);
    }

    protected function processStatements(Connection $connection, array $statements): void
    {
        foreach ($statements  as $statement) {
            $this->processStatement($connection, $statement);
        }
    }

    private function processStatement(Connection $connection, string $statement): void
    {
        try {
            $connection->execute($statement);
        } catch (DatasourceException $ex) {
            $this->io->status('error', str_replace("\n", '', $statement));
            $this->throwError('Executing query failed', $ex->getMessage());
        }
        $this->io->status('ok', str_replace("\n", '', $statement));
    }

    abstract public function throwError(string $title, string $message = null): void;
}
