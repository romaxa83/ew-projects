<?php

namespace App\Console\Commands\Helpers\Gmail;

use App\Models\Mailbox\Gmail\Account;
use Dacastro4\LaravelGmail\Facade\LaravelGmail;
use Dacastro4\LaravelGmail\LaravelGmailClass;
use Illuminate\Console\Command;

class ReconnectAccount extends Command
{
    protected $signature = 'helpers:gmail-reconnect-account';

    public function handle()
    {
        $id = $this->ask('Account id');

        try {
            $model = Account::query()
                ->findOrFail($id);

            LaravelGmail::setUserId($model->id)->makeToken();


            dd('f');
//            $mailbox = new LaravelGmailClass(config(), $model->id);
            $mailbox = new LaravelGmailClass(config(), $model->id);
            $mailbox->makeToken();


            dd($model);

        } catch (\Throwable $e) {
            dd($e);
        }

        return self::SUCCESS;
    }
}
