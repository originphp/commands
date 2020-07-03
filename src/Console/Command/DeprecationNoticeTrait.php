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

trait DeprecationNoticeTrait
{
    protected function checkForDeprecations() : void
    {
        if (!Config::exists('App.schemaFormat')) {
            deprecationWarning('The Schema.format setting is deprecated use App.schemaFormat instead.');
        }
    }
}
