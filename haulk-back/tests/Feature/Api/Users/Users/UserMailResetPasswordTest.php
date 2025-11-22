<?php

namespace Tests\Feature\Api\Users\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserMailResetPasswordTest extends TestCase
{

    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_send_email_for_password_reset_success()
    {
        $user = $this->dispatcherFactory();

        $this->loginAsCarrierDispatcher($user);

        $this->postJson(route('password.forgot'), ['email' => $user->email])
            ->assertOk();

        //Не нашел возможности протестировать отправку почты;
    }
}
