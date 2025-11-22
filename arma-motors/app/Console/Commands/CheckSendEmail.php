<?php

namespace App\Console\Commands;

use App\Notifications\TestNotifications;
use App\Repositories\Admin\AdminRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CheckSendEmail extends Command
{
    public const QUESTION_EMAIL = 'Email: ';

    protected $signature = 'am:send-email';

    protected $description = 'Check for send test email';

    public function handle(AdminRepository $adminRepository)
    {
        try {

            $args = [
                'email' => $this->ask(self::QUESTION_EMAIL),
            ];

            Notification::route('mail', (string)$args['email'])
                ->notify(new TestNotifications());

            $this->info("[✔] - email send to - {$args['email']}");
        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }

    protected function validation(array $args): void
    {
        Validator::validate(
            $args,
            [
                'email' => ['required', 'email'],
            ]
        );
    }
}

