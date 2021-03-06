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
use Origin\Inflector\Inflector;
use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;

class DbSchemaDumpCommand extends Command
{
    use DbSchemaTrait, DeprecationNoticeTrait;
    
    protected $name = 'db:schema:dump';
    protected $description = 'Dumps the database schema to file';

    /**
     * @var string
     */
    protected $template =
    '<?php
use Origin\Model\Schema;

class %name%Schema extends Schema
{
    const VERSION = %version%;

%define%
}
';

    protected function initialize(): void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection',
            'short' => 'c',
            'default' => 'default',
        ]);

        /**
         * @deprecated Schema.format
         */
        $this->addOption('type', [
            'description' => 'How the schema will be dumped, in sql or php',
            'default' => Config::read('App.schemaFormat')
        ]);
        $this->addArgument('name', [
            'description' => 'schema_name or Plugin.schema_name',
        ]);
        $this->checkForDeprecations();
    }
 
    protected function execute(): void
    {
        $name = $this->arguments('name') ?? 'schema';

        $datasource = $this->options('connection');
        if (! ConnectionManager::config($datasource)) {
            $this->throwError("{$datasource} connection not found");
        }

        $type = $this->options('type');
        if (! in_array($type, ['sql','php'])) {
            $this->throwError(sprintf('The type `%s` is invalid', $type));
        }

        $filename = $this->schemaFilename($name, $type);
        $database = str_replace(ROOT . '/', '', ConnectionManager::get($datasource)->database());

        $this->io->info("Dumping database `{$database}` schema to {$filename}");
        if ($type === 'sql') {
            $this->dump($datasource, $name);
        } else {
            $this->dumpPhp($datasource, $name);
        }
    }

    /**
     * Dumps the schema to SQL format
     *
     * @param string $datasource
     * @param string $name
     * @return void
     */
    protected function dump(string $datasource, string $name): void
    {
        $connection = ConnectionManager::get($datasource);
        $dump = [];
        $filename = $this->schemaFilename($name, 'sql');
     
        /**
         * I would like to use pg_dump, however I started getting version matching errors so
         * therefore I am not sure this is going to be good
         * @example shell_exec("pg_dump -h {$config['host']} -s {$config['database']} -U {$config['username']}");
         */
        //
        foreach ($connection->tables() as $table) {
            $dump[] = $connection->adapter()->showCreateTable($table) . ';';
            $this->io->list($table);
        }

        if (! $this->io->createFile($filename, implode("\n\n", $dump))) {
            $this->throwError('Error saving schema file');
        }
    }

    /**
     * Dumps the schema to agnostic version
     *
     * @param string $datasource
     * @param string $name
     * @return void
     */
    protected function dumpPhp(string $datasource, string $name): void
    {
        $filename = $this->schemaFilename($name, 'php');
        list($plugin, $name) = pluginSplit($name);
        $className = 'Application';
        if ($name !== 'schema') {
            $className = Inflector::studlyCaps($name);
        }
       
        $connection = ConnectionManager::get($datasource);
        $out = [];
        $tables = $connection->tables();
        foreach ($tables as $table) {
            $data = $connection->adapter()->describe($table);
            $this->io->list($table);
            $columns = [];
            $columns[] = $this->datasetToString('columns', $data['columns']);
            $columns[] = $this->datasetToString('constraints', $data['constraints']);
            $columns[] = $this->datasetToString('indexes', $data['indexes']);

            if (isset($data['options'])) {
                $options = $this->values($data['options']);
                $columns[] = "\t\t'options' => " . '[' . implode(', ', $options) . ']';
            }

            $out[] = "\tpublic \${$table} = [\n" . implode(",\n", $columns) .  "\n\t];\n" ;
        }
        $template = str_replace('%version%', date('Ymdhis'), $this->template);
        $template = str_replace('%define%', implode("\n", $out), $template);
        $template = str_replace('%name%', $className, $template);
    
        if (! $this->io->createFile($filename, $template)) {
            $this->throwError('Error saving schema file');
        }
    }

    /**
     * Converts a dataset to string
     *
     * @param string $key
     * @param array $data
     * @return string
     */
    protected function datasetToString(string $key, array $data): string
    {
        $out = '[]';
        if ($data) {
            $out = [];
            foreach ($data as $name => $definition) {
                $column = $this->values($definition);
                $out[] = "\t\t'{$name}' => " . '[' . implode(', ', $column) . ']';
            }
            $out = "[\n\t" . implode(",\n\t", $out) . "\n\t\t]";
        }

        return "\t\t'{$key}' => " . $out;
    }

    /**
     * Process values recursively
     *
     * @param array $data
     * @return array
     */
    protected function values(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $out[] = "'{$key}' => [" . implode(', ', $this->values($value)) . ']';
            } else {
                $value = var_export($value, true);
                if (is_string($key)) {
                    $out[] = "'{$key}' => {$value}";
                } else {
                    $out[] = $value;
                }
            }
        }

        return $out;
    }
}
