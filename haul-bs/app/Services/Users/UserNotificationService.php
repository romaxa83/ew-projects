<?php

namespace App\Services\Users;

use App\Events\Events\Customers\CreateCustomerTaxExemptionEComEvent;
use App\Models\Customers\Customer;
use App\Models\Users\User;
use App\Notifications\Auth\ChangePasswordNotification;
use App\Notifications\Auth\ForgotPasswordNotification;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Users\UserTaxExemptionNotification;
use Illuminate\Support\Facades\Notification;

class UserNotificationService
{
    public function __construct(protected VerificationService $verificationService)
    {}

    public function changePassword(User $model): void
    {
        Notification::route('mail', $model->email->getValue())
            ->notify(new ChangePasswordNotification($model));
    }

    public function forgotPassword(User $model): void
    {
        $link = $this->verificationService->getLinkForPasswordReset($model);

        Notification::route('mail', $model->email->getValue())
            ->notify(new ForgotPasswordNotification($model, $link));
    }

    public function resetPassword(User $model, string $password): void
    {
        Notification::route('mail', $model->email->getValue())
            ->notify(new ResetPasswordNotification($model, $password)
        );
    }

    public function createdTaxExemption(string $email, Customer $customer): void
    {
        $link = str_replace('{id}', $customer->id, config('routes.front.bs_open_customer'));
        Notification::route('mail', $email)
            ->notify(new UserTaxExemptionNotification($customer, $link));
    }
}
