<?php

namespace App\Jobs;

use App\Notifications\SendReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailSendReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        if(!isset($this->data['email']) || !isset($this->data['link'])){
            throw new \InvalidArgumentException("There is no email address or link in the job data");
        }

        \Notification::route('mail', $this->data['email'])
            ->notify(new SendReport($this->data['link']));
    }
}

