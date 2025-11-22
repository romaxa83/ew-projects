<?php

namespace App\Providers;

use App\Events\Admin\AdminLogged;
use App\Events\Admin\GeneratePassword;
use App\Events\ChangeHashEvent;
use App\Events\Firebase\FcmPush;
use App\Events\Order\AcceptAgreementEvent;
use App\Events\Order\CreateOrder;
use App\Events\SmsVerify\SendSmsCode;
use App\Events\User\EditUser;
use App\Events\User\EmailConfirm;
use App\Events\User\NotUserFromAA;
use App\Events\User\SaveCarFromAA;
use App\Events\User\SendCarDataToAA;
use App\Events\User\UserConfirmEmail;
use App\Listeners\Admin\AdminLoggedListeners;
use App\Listeners\Admin\GeneratePasswordListeners;
use App\Listeners\ChangeHashListeners;
use App\Listeners\Firebase\FcmPushListeners;

use App\Listeners\Order\AcceptAgreementListeners;
use App\Listeners\Order\SendOrderToAAListeners;
use App\Listeners\SmsVerify\SendSmsCodeListeners;
use App\Listeners\User\AttachLoyaltyListeners;
use App\Listeners\User\EmailConfirmListeners;
use App\Listeners\User\SendCarDataToAAListeners;
use App\Listeners\User\SendDataToUpdateUserListeners;
use App\Listeners\User\SendUserDataToAAListeners;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [SendEmailVerificationNotification::class,],
        AdminLogged::class => [AdminLoggedListeners::class,],
        GeneratePassword::class => [GeneratePasswordListeners::class,],
        SendSmsCode::class => [SendSmsCodeListeners::class,],
        EmailConfirm::class => [EmailConfirmListeners::class,],
        FcmPush::class => [FcmPushListeners::class,],
        ChangeHashEvent::class => [ChangeHashListeners::class,],
        NotUserFromAA::class => [SendUserDataToAAListeners::class,],
        SendCarDataToAA::class => [SendCarDataToAAListeners::class,],
        SaveCarFromAA::class => [AttachLoyaltyListeners::class,],
        UserConfirmEmail::class => [SendDataToUpdateUserListeners::class,],
        EditUser::class => [SendDataToUpdateUserListeners::class,],
        CreateOrder::class => [SendOrderToAAListeners::class,],

        AcceptAgreementEvent::class => [AcceptAgreementListeners::class,],
    ];

    public function boot(): void
    {}
}
