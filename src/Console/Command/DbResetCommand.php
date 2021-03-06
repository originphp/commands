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

use Origin\Console\Command\Command;

class DbResetCommand extends Command
{
    use DeprecationNoticeTrait;

    protected $name = 'db:reset';

    protected $description = 'Drops the database and then runs setup';

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

        /**
         * @deprecated Schema.format
         */
        $this->addOption('type', [
            'description' => 'Use sql or php file',
            'default' => Config::read('App.schemaFormat')
        ]);

        $this->checkForDeprecations();
    }
 
    protected function execute(): void
    {
        $datasource = $this->options('connection');
        $name = $this->arguments('name') ?? 'schema';

        $this->runCommand('db:drop', [
            '--connection' => $datasource,
        ]);
       
        $this->runCommand('db:setup', [
            '--connection' => $datasource,
            '--type' => $this->options('type'),
            $name,
        ]);
    }
}
