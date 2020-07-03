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

class DbSetupCommand extends Command
{
    use DeprecationNoticeTrait;

    protected $name = 'db:setup';
    protected $description = 'Creates the database,loads schema and seeds the database';

    protected function initialize(): void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection',
            'short' => 'c',
            'default' => 'default',
        ]);
        $this->addArgument('name', [
            'description' => 'schema_name or Plugin.schema_name',
        ]);
        $this->addOption('type', [
            'description' => 'Use sql or php file',
            'default' => Config::read('App.schemaFormat') ?? Config::read('Schema.format'),
        ]);
        $this->checkForDeprecations();
    }
 
    protected function execute(): void
    {
        $name = $this->arguments('name') ?? 'schema';

        # Create arguments
        $schema = $name;
        $seed = 'seed';
        # Have to use seed here
        list($plugin, $null) = pluginSplit($name);
        if ($plugin) {
            $seed = "{$plugin}.seed";
        }
    
        $datasource = $this->options('connection');
        $this->runCommand('db:create', [
            '--connection' => $datasource,
        ]);
   
        $this->io->nl();
    
        $this->runCommand('db:schema:load', [
            '--connection' => $datasource,
            '--type' => $this->options('type'),
            $schema,
        ]);
     
        $this->io->nl();

        $this->runCommand('db:seed', [
            '--connection' => $datasource,
            '--type' => $this->options('type'),
            $seed,
        ]);
    }
}
