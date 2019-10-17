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
namespace Commands\Test\Console\Command;

use Origin\TestSuite\ConsoleIntegrationTestTrait;

class LocaleGenerateCommandTest extends \PHPUnit\Framework\TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testRun()
    {
        $this->recursiveDelete(CONFIG . DS . 'locales'); // reach path
        
        $this->exec('locale:generate --force');
        $this->assertExitSuccess();
        $output = $this->output();
        $this->assertRegExp('/Generated ([0-9]{3}) locale definitions/', $output); // Different systems generate different amounts
      
        // Remove files
        $this->recursiveDelete(CONFIG . DS . 'locales'); // reach path
    }

    public function testQualityCheck()
    {
        $this->exec('locale:generate en_GB --force');
        $this->assertExitSuccess();
        $this->assertOutputContains('Generated 1 locale definitions');

        // $hash = md5(file_get_contents('/var/www/config/locales/en_GB.php'));
        $this->assertEquals('00bfc52abca78a0c72d0156af190bac6', md5(file_get_contents(CONFIG . DS . 'locales' . DS . 'en_GB.php')));
        # Dont DELETE THIS. This is used by other tests
    }

    public function testGenerateSingleFile()
    {
        $path = CONFIG . DS . 'locales' . DS . 'locales.php';

        $this->exec('locale:generate --single-file --force --expected en_GB en_US es_ES fr_FR');
        $this->assertExitSuccess();
        $this->assertOutputContains('Generated 4 locale definitions');
        $this->assertFileExists($path);
        unlink($path);
    }

    /**
     * This is slow implementation
     *
     * @param string $directory
     * @return void
     */
    private function recursiveDelete(string $directory)
    {
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $filename) {
            if (is_dir($directory . DS . $filename)) {
                $this->recursiveDelete($directory . DS . $filename);
                continue;
            }
            unlink($directory . DS . $filename);
        }

        return rmdir($directory);
    }
}
