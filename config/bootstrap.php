<?php

/**
 * This is the bootstrap for plugin when using as standalone (for development). Do not
 * use this bootstrap as a plugin. .gitattributes has blocked this from being installed.
 */

use Origin\Cache\Cache;
use Origin\Cache\Engine\FileEngine;
use Origin\Job\Queue;
use Origin\Core\Config;
use Origin\Model\ConnectionManager;
use Origin\Mailbox\Mailbox;

require __DIR__ . '/paths.php';
require dirname(__DIR__) . '/vendor/originphp/Core/bootstrap.php';

Config::write('App.debug', env('APP_DEBUG', true));
Config::write('App.namespace', 'Commands');
Config::write('App.schemaFormat', 'php');
Config::write('App.mailboxKeepEmails', '+30 days');

ConnectionManager::config('default', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'engine' => env('DB_ENGINE', 'mysql')
]);

ConnectionManager::config('test', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'engine' => env('DB_ENGINE', 'mysql')
]);

Queue::config('default', [
    'engine' => 'Database',
    'connection' => 'default'
]);

Queue::config('test', [
    'engine' => 'Database',
    'connection' => 'test'
]);

Queue::config('test', [
    'engine' => 'Database',
    'connection' => 'test'
]);

Cache::config('origin', [
    'className' => FileEngine::class,
    'path' => CACHE . '/origin',
    'duration' =>  '+2 minutes',
    'prefix' => 'cache_',
    'serialize' => true
]);

Mailbox::config('default', [
    'host' => env('IMAP_HOST', '127.0.0.1'),
    'port' => env('IMAP_PORT', 143),
    'username' => env('IMAP_USERNAME'),
    'password' => env('IMAP_PASSWORD'),
    'encryption' => env('IMAP_ENCRYPTION'),
    'validateCert' => false,
    'timeout' => 5
]);
