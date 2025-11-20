<?php

namespace App\Notifications;

use App\Models\User\User;
use App\Traits\Mail\ActionIntoLine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Action;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class SendResetPassword extends Notification
{
    use Queueable;
    use ActionIntoLine;

    private $password;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
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
            ->subject("New password for the application {$appName}")
            ->line(new HtmlString('<strong>Your password</strong> - ' . $this->password))
            ->action('Go to download ios app', "{$iosLink}")
            ->line($this->makeActionIntoLine(new Action('Go to download android app', $androidLink)))
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
