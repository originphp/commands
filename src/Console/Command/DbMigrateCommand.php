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

use Origin\Model\Model;
use Origin\Console\Command\Command;
use Origin\Core\Exception\Exception;
use Origin\Model\Concern\Timestampable;

class Migration extends Model
{
    use Timestampable;
}

class DbMigrateCommand extends Command
{
    protected $name = 'db:migrate';
    protected $description = 'Runs and rollsback migrations';

    /**
     * Undocumented variable
     *
     * @var \Origin\Migration\Migration
     */
    protected $Migration = null;

    protected function initialize(): void
    {
        $this->addOption('connection', [
            'description' => 'Use a different connection',
            'short' => 'c',
            'default' => 'default',
        ]);
        $this->addArgument('version', [
            'description' => 'a target version e.g. 20190511111934','type' => 'integer'
        ]);
    }
 
    protected function execute(): void
    {
        $version = $this->arguments('version');

        # Dynamically Create Migration Model for CRUD
        $this->Migration = $this->loadModel('Migration', [
            'className' => Migration::class,
            'connection' => $this->options('connection')
        ]);
      
        $lastMigration = $this->lastMigration();
        if ($version === null || $version > $lastMigration) {
            $this->migrate($version);
        } else {
            $this->rollback($version);
        }

        $this->runCommand('cache:clear', ['--quiet']);
    }

    /**
     * Migrates
     *
     * @param integer $version
     * @return void
     */
    private function migrate(int $version = null): void
    {
        $migrations = $this->getMigrations($this->lastMigration(), $version);
        if (empty($migrations)) {
            $this->io->warning('No migrations found');

            return;
        }
        $start = microtime(true);
        
        $count = 0;
        foreach ($migrations as $object) {
            $this->out("<notice>{$object->name}</notice> [<yellow>{$object->version}</yellow>]");
            try {
                $migration = $this->createMigration($object);

                $this->verboseStatements($migration->start());
             
                $entity = $this->Migration->new([
                    'version' => $object->version,
                    'rollback' => json_encode($migration->reverseStatements()),
                ]);
         
                $this->Migration->save($entity);
                $count++;
            } catch (Exception $ex) {
                $this->throwError($ex->getMessage());
            }
        }

        $this->io->success(sprintf('Migration Complete. %d migrations in %d ms', $count, (microtime(true) - $start)));
    }

    /**
     * Rollsback
     *
     * @param int $version
     * @return void
     */
    private function rollback(int $version): void
    {
        $migrations = $this->getMigrations($version, $this->lastMigration());
        $migrations = array_reverse($migrations);
  
        if (empty($migrations)) {
            $this->io->warning('No migrations found');

            return;
        }
        $count = 0;
        $start = microtime(true);
        foreach ($migrations as $object) {
            $this->out("<red>{$object->name}</red> [<yellow>{$object->version}</yellow>]");
            try {
                $migration = $this->createMigration($object);
                $entity = $this->Migration->find('first', ['conditions' => ['version' => $object->version]]);
                /**
                 * Do the magic
                 */
                $reverse = [];
                if ($entity->rollback) {
                    $reverse = json_decode($entity->rollback, true);
                }
                
                $this->verboseStatements($migration->rollback($reverse));
              
                $this->Migration->delete($entity);
                $count++;
            } catch (Exception $ex) {
                $this->throwError($ex->getMessage());
            }
        }
        $this->io->success(sprintf('Rollback Complete. %d migrations in %d ms', $count, (microtime(true) - $start)));
    }

    /**
     * Creates the object
     *
     * @param object $object
     * @return \Origin\Migration\Migration;
     */
    private function createMigration(object $object)
    {
        include_once $object->file;
        $adapter = $this->Migration->connection()->adapter();

        return new $object->class($adapter);
    }

    /**
     * Gets the last migration version
     *
     * @return int|null
     */
    private function lastMigration(): ?int
    {
        $lastMigration = $this->Migration->find('first', ['order' => 'version DESC']);
        if ($lastMigration) {
            return (int) $lastMigration->version;
        }

        return null;
    }

    /**
     * Verboses statements to screen
     *
     * @param array $statements
     * @return void
     */
    private function verboseStatements(array $statements): void
    {
        $this->out('');
        foreach ($statements as $statement) {
            $this->out(sprintf('<green> > </green><text>%s</text>', $statement));
            $this->out('');
        }
    }

    /**
     * Returns an array of migrations
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    private function getMigrations(int $from = null, int $to = null): array
    {
        foreach ($this->paths() as $path) {
            if (! is_dir($path)) {
                continue;
            }
            $results = array_diff(scandir($path), ['.', '..']);
            $migrations = [];
            foreach ($results as $file) {
                $class = pathinfo($file, PATHINFO_FILENAME);
             
                if (preg_match('/^([0-9]{14})(.*)/', $class, $matches)) {
                    $version = $matches[1];
                    if (($from && $version <= $from) || ($to && $version > $to)) {
                        continue;
                    }
                    $migrations[] = (object) [
                        'name' => $matches[2],
                        'version' => $matches[1],
                        'class' => $matches[2] .'Migration',
                        'file' => $path . '/' . $file,
                    ];
                }
            }
        }

        return $migrations;
    }

    /**
     * Gets the paths to scan
     *
     * @return array
     */
    private function paths(): array
    {
        $folder = '/database/migrations';

        $paths = [
            ROOT . $folder
        ];

        foreach (Plugin::loaded() as $plugin) {
            $paths[] = Plugin::path($plugin) . $folder;
        }

        return $paths;
    }
}
