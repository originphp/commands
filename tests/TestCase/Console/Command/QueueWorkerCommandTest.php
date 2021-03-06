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

use Origin\Job\Job;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class PassOrFailJob extends Job
{
    protected $connection = 'test';

    public function initialize(): void
    {
        $this->onError('errorHandler');
    }

    public function execute(bool $pass = true)
    {
        if (! $pass) {
            $value = $abc['foo'];
        }
    }
    public function errorHandler(\Exception $exception): void
    {
        $this->retry(['wait' => '+1 second','limit' => 1]);
    }
}

class QueueWorkerCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;
    
    protected $fixtures = ['Queue'];

    public function testRunNothingInQueue()
    {
        $this->exec('queue:worker --connection=test');
        $this->assertExitSuccess();
    }

    public function testRunJobSuccessful()
    {
        // Create a job and dispatch
        (new PassOrFailJob())->dispatch(true);
        $this->exec('queue:worker --connection=test');
        
        $this->assertExitSuccess();
        $this->assertOutputContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<pass> OK </pass>');
    }

    public function testRunJobFail()
    {
        // Create a job and dispatch
        (new PassOrFailJob())->dispatch(false);
        $this->exec('queue:worker --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<fail> FAILED </fail>');
    }

    public function testRunJobFailRetry()
    {
        // Create a job and dispatch
        (new PassOrFailJob())->dispatch(false);
        $this->exec('queue:worker --connection=test');
      
        $this->assertExitSuccess();
        $this->assertOutputContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<fail> FAILED </fail>');
        $this->assertOutputNotContains('Retry');

        sleep(1);

        // Second time should be retry
        $this->exec('queue:worker --connection=test');
        $this->assertExitSuccess();
        
        $this->assertOutputNotContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<cyan>Retry #1</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<fail> FAILED </fail>');
    }

    public function testMaintenanceMode()
    {
        file_put_contents(tmp_path('maintenance.json'), json_encode([]));
     
        (new PassOrFailJob())->dispatch(true);
        $this->exec('queue:worker --connection=test');
        
        // Test that the job is not run
        $this->assertExitSuccess();
        $this->assertOutputNotContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputNotContains('<pass> OK </pass>');
        $this->deleteFile(tmp_path('maintenance.json'));

        // Test that job is now run when not in maintenance mode
        $this->exec('queue:worker --connection=test');
        $this->assertExitSuccess();
        $this->assertOutputContains('<cyan>Run</cyan> <text>PassOrFail</text>');
        $this->assertOutputContains('<pass> OK </pass>');
    }

    public function testMaxSeconds()
    {
        (new PassOrFailJob())->dispatch(true);
     
        $started = time();

        $this->exec('queue:worker -d --seconds=2 --sleep=0 --connection=test');

        $took = time() - $started ;

        $this->assertGreaterThanOrEqual(2, $took);
    }

    /*
     * @depends testMaxSeconds
     */
    public function testSleep()
    {
        $started = time();

        $this->exec('queue:worker -d --seconds=1 --sleep=2');

        $took = time() - $started ;

        $this->assertGreaterThanOrEqual(2, $took);
    }


    public function deleteFile(string $file) : void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
