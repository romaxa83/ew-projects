<?php

namespace App\Notifications\Orders;

use App\Models\Attachment;
use App\Models\Employee;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class SendEstimateToForeman extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public Attachment $attachment,
        public Employee $foreman
    )
    {
    }

    public function via(): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(): MailMessage
    {
        $subject = 'H2H Movers';
        $docName = 'estimate';
        if(config('mail.dev-subject')){
            $subject = '[dev-est-foreman] ' . $subject;
        }

        $dataForEmail = config('division')[$this->order->division_id];

        $pathToFile = Storage::path($this->attachment->miscs['file']['patch'] . $this->attachment->hash);

        $addressFrom = config('mail.from.address');

        $mail = (new MailMessage)
            ->from($addressFrom, email_app_name())
            ->subject($subject)
            ->greeting(
                "Dear " . $this->order->client->full_name
            )
            ->line('Please see your '. $docName .' attached to this letter.')
            ->line('Sincerely')
            ->line('H2H movers, Inc.')
            ->line(new HtmlString('<a href="' . url($dataForEmail['address']['map_url']) . '">'. $dataForEmail['address']['name'] .'</a>'))
            ->line('Phone: ' . $dataForEmail['phone'])
            ->line(new HtmlString('Web: ' . '<a href="' . url($dataForEmail['site']['url']) . '">'. $dataForEmail['site']['name'] .'</a>'))
            ->line($dataForEmail['license'])
            ->attach($pathToFile, [
                'as' => $this->attachment->miscs['file']['name'],
                'mime' => 'application/pdf', // MIME-тип файла
            ])
        ;

        return $mail;
    }
}
