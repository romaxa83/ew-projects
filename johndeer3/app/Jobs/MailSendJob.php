<?php

namespace App\Jobs;

use App\Notifications\SendLoginPassword;
use App\Notifications\SendResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Notification;

class MailSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $this->checkData();

        if($this->data['type'] == 'password'){
            Notification::send($this->data['user'], new SendLoginPassword($this->data['user'], $this->data['password']));
        }
        if($this->data['type'] == 'reset-password'){
            Notification::send($this->data['user'], new SendResetPassword($this->data['user'], $this->data['password']));
        }
    }

    public function checkData(): void
    {
        $msg = null;
        if(!isset($this->data['type']) && $msg == null){
            $msg = "There is no 'type' in the job data";
        }
        if(!isset($this->data['user']) && $msg == null){
            $msg = "There is no 'user' in the job data";
        }
        if(!isset($this->data['password']) && $msg == null){
            $msg = "There is no 'password' in the job data";
        }
        if($msg != null){
            throw new \InvalidArgumentException($msg);
        }
    }
}
