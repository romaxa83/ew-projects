<?php

namespace App\Notifications;

use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Traits\Mail\ActionIntoLine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Action;

class SendIosLink extends Notification
{
    use Queueable;
    use ActionIntoLine;

    private $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = prettyAppName();
        $address = config('mail.from.address');

        $androidLink = config('app.android_link');
        $iosLink = $this->user->ios_link;

        return (new MailMessage)
            ->from($address, $appName)
            ->greeting("Hello, {$this->user->fullName()}")
            ->subject("Your login and password to enter the application {$appName}")
            ->line(new HtmlString('<strong>Your login</strong> - ' . $this->user->login))
            ->line(new HtmlString('<strong>Your password</strong> - ' . 'use your password'))
//            ->line('Good day,
//This letter was sent to you to inform iOS users that they need to remove the application from TestFlight, follow the link in the letter and download the application from the App Store.
//Android users have had the application on their phones since the launch (Beta). We published the application (publicly), you need to delete the old version and download the new one from the corresponding link. ')
            ->action('Go to download ios app', "{$iosLink}")
            ->line($this->makeActionIntoLine(new Action('Go to download android app', $androidLink)))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
