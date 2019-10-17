<?php

/**
 * This is the bootstrap for plugin when using as standalone (for development). Do not
 * use this bootstrap as a plugin. .gitattributes has blocked this from being installed.
 */
use Origin\Core\Config;
use Origin\Job\Queue;
use Origin\Model\ConnectionManager;

require __DIR__ . '/paths.php';
require ORIGIN . '/src/bootstrap.php';

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

Queue::config('test', [
    'engine' => 'Database',
    'connection' => 'test'
]);
