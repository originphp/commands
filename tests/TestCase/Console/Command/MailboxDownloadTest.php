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
namespace Commands\Test\Console\Command;

use Origin\Mailbox\Mailbox;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;
use Origin\Core\Exception\InvalidArgumentException;

class MailboxDownloadTest extends OriginTestCase
{
    public $fixtures = ['Mailbox','Imap','Queue'];

    use ConsoleIntegrationTestTrait;

    public function testUnkownAccount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->exec('mailbox:download foo');
        $this->assertExitError();
    }

  
    public function testInvalidAccount()
    {
        if (! extension_loaded('imap')) {
            $this->markTestSkipped();
        }

        Mailbox::config('foo', [
            'host' => 'localhost',
            'username' => 'nobody',
            'password' => '1234'
        ]);
        $this->exec('mailbox:download foo -v');
        $this->assertOutputContains('ERROR');
    }
    
    public function testDownloadMessages()
    {
        if (! extension_loaded('imap')) {
            $this->markTestSkipped();
        }

        if (! getenv('IMAP_HOST') ||! getenv('IMAP_USERNAME') || ! getenv('IMAP_PASSWORD')) {
            $this->markTestSkipped();
        }
        
        $this->exec('mailbox:download -v');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/Downloaded ([0-9]+) message/');
    }

    public function testMaintenanceMode()
    {
        file_put_contents(tmp_path('maintenance.json'), json_encode([]));
        $this->exec('mailbox:download -v');
        $this->assertExitSuccess();
        $this->assertErrorContains('Maintenance mode is enabled, emails will not be downloaded.');
        $this->deleteFile(tmp_path('maintenance.json'));
    }

    public function deleteFile(string $file) : void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
