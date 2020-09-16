<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright     Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);
namespace Commands\Console\Command;

use Origin\Cache\Cache;
use Origin\Console\Command\Command;

class CacheClearCommand extends Command
{
    protected $name = 'cache:clear';
    protected $description = 'Clears the cache for all configured stores.';
 
    protected function execute(): void
    {
        $caches = Cache::config();
        foreach ($caches as $name => $config) {
            $result = Cache::clear(['config' => $name]);
            $this->io->status($result ? 'ok' : 'error', $name);
        }

        if (file_exists(CONFIG .'/.env.php')) {
            $result = unlink(CONFIG .'/.env.php');
            $this->io->status($result ? 'ok' : 'error', 'config/.env.php');
        }
    }
}
