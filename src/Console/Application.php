<?php
/**
 * Application initialization for
 */
namespace Commands\Console;

use Origin\Console\BaseApplication;
use Origin\Core\Config;

/**
 * @codeCoverageIgnore
 */
class Application extends BaseApplication
{
    /**
     * Constructor hook
     *
     * @return void
     */
    protected function initialize(): void
    {
        if (!Config::exists('App.schemaFormat')) {
            deprecationWarning('The Schema.format setting is deprecated use App.schemaFormat instead.');
        }
        
        if (!Config::exists('App.mailboxKeepEmails')) {
            deprecationWarning('The mailboxKeepEmails setting is deprecated use App.mailboxKeepEmails instead.');
        }
    }
}
