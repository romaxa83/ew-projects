<?php

namespace App\Jobs;

use App\Notifications\SendIosLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Notification;

class MailSendIosLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        if(!isset($data['user'])){
            throw new \InvalidArgumentException("There is no 'user' in the job data");
        }

        $this->data = $data;
    }

    public function handle(): void
    {
        Notification::send($this->getUser(), new SendIosLink($this->getUser()));
    }

    public function getUser()
    {
        return $this->data['user'];
    }
}

