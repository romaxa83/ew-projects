<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Notifications\DriverRegistrationEmail;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Notification;

class UserNotificationService
{
    use ResetsPasswords;

    public function send(User $user): void
    {
        if ($user->isDriver()) {
            $token = app('auth.password.broker')->createToken($user);
            Notification::send($user, new DriverRegistrationEmail($user, $token));
        } else {
            $this->broker()->sendResetLink(['email' => $user->email]);
        }
    }
}
