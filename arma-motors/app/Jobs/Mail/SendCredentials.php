<?php

namespace App\Jobs\Mail;

use App\DTO\Admin\AdminDTO;
use App\Notifications\Mail\CredentialsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Notification;

class SendCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public AdminDTO $dto)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::route('mail', (string)$this->dto->getEmail())
            ->notify(new CredentialsNotification($this->dto));
    }
}
