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

use Exception;
use Origin\Mailbox\Mailbox;
use Origin\Console\Command\Command;
use Origin\Mailbox\Model\ImapMessage;
use Origin\Mailbox\Model\InboundEmail;

class MailboxDownloadCommand extends Command
{
    protected $name = 'mailbox:download';
    protected $description = [
        'Downloads messages for the mailboxes'
    ];

    protected function initialize(): void
    {
        $this->addArgument('account', [
            'description' => 'An account name or a list of accounts seperated by spaces',
            'type' => 'array'
        ]);
        $this->loadModel('InboundEmail', ['className' => InboundEmail::class]);
        $this->loadModel('Imap', ['className' => ImapMessage::class]);
    }
 
    /**
     * Main method for Command
     *
     * @return void
     */
    protected function execute(): void
    {
        $accounts = $this->arguments('account') ?? ['default'];
     
        foreach ($accounts as $account) {
            $this->debug('<green>> </green><white>Checking `' . $account . '` email account</white>');
            
            // Check email account exists or throw error out of try block
            Mailbox::account($account);

            try {
                $result = Mailbox::download($account);
                $this->io->status('ok', $account);
                $count = count($result->data);
                $this->debug('<green>> </green>Downloaded ' . $count . ' ' . __('message|messages', ['count' => $count]));
            } catch (Exception $exception) {
                $this->io->status('error', $account);
                $this->debug('<green>> </green> <white>' . $exception->getMessage() .'</white>');
            }
        }
    }
}
