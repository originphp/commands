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
namespace Commands\Test\TestCase\Console\Command;

use Origin\Cache\Cache;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class CacheClearCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testExecution()
    {
        Cache::config('default', ['engine' => 'Array']);
        Cache::write('foo', 'bar');
        $this->assertEquals('bar', Cache::read('foo'));

        $this->exec('cache:clear'); // Inject data

        $this->assertExitSuccess();
        $this->assertOutputContains('default');
        $this->assertNull(Cache::read('foo'));
    }
}
