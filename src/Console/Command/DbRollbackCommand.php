<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
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

use Origin\Model\Model;
use Origin\Console\Command\Command;

class DbRollbackCommand extends Command
{
    protected $name = 'db:rollback';
    protected $description = 'Rollsback the last migration';

    /**
     * Undocumented variable
     *
     * @var \Origin\Migration\Migration
     */
    protected $Migration = null;

    protected function initialize() : void
    {
        $this->addOption('connection', [
            'description' => 'Use a different datasource','short' => 'c','default' => 'default',
        ]);
    }
 
    protected function execute() : void
    {
        $this->Migration = new Model([
            'name' => 'Migration',
            'connection' => $this->options('connection'),
        ]);

        $lastMigration = $this->lastMigration();
        if ($lastMigration) {
            $this->runCommand('db:migrate', [$lastMigration - 1,'--connection' => $this->options('connection')]);
        } else {
            $this->io->warning('No migrations found');
        }
    }

    /**
     * Gets the last migration version
     *
     * @return int|null
     */
    private function lastMigration() : ?int
    {
        $lastMigration = $this->Migration->find('first', ['order' => 'version DESC']);
        if ($lastMigration) {
            return $lastMigration->version;
        }

        return null;
    }
}
