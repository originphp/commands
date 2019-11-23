<?php

/**
 * This is the bootstrap for plugin when using as standalone (for development). Do not
 * use this bootstrap as a plugin. .gitattributes has blocked this from being installed.
 */
use Origin\Job\Queue;
use Origin\Core\Config;
use Origin\Model\ConnectionManager;
use Origin\Mailbox\Mailbox;

require __DIR__ . '/paths.php';
require ORIGIN . '/src/bootstrap.php';

/**
 * Load environment vars
 */
if (file_exists(__DIR__ . '/.env.php')) {
    $result = require __DIR__ . '/.env.php';
    foreach ($result as $key => $value) {
        $_ENV[$key] = $value;
    }
}

Config::write('debug', env('APP_DEBUG', true));
Config::write('App.namespace', 'Commands');
Config::write('Schema.format', 'php');

ConnectionManager::config('default', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => 'commands',
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'engine' => env('DB_ENGINE', 'mysql')
]);

ConnectionManager::config('test', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => 'commands',
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

Mailbox::config('default', [
    'host' => env('IMAP_HOST', '127.0.0.1'),
    'port' => env('IMAP_PORT', 143),
    'username' => env('IMAP_USERNAME'),
    'password' => env('IMAP_PASSWORD'),
    'encryption' => env('IMAP_ENCRYPTION'),
    'validateCert' => false,
    'timeout' => 5
]);
