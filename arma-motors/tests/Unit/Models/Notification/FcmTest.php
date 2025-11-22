<?php

namespace Tests\Unit\Models\Notification;

use App\Exceptions\EmailVerifyException;
use App\Models\Admin\Admin;
use App\Models\Notification\Fcm;
use App\Models\Permission\Role;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class FcmTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function set_error()
    {
        $user = $this->userBuilder()->create();
        $fcm = Fcm::factory()->create(['user_id' => $user->id]);
        $fcm->refresh();

        $this->assertFalse($fcm->hasError());
        $this->assertNull($fcm->response_data);

        $error = 'some_error';
        $fcm->setError($error);
        $fcm->refresh();

        $this->assertTrue($fcm->hasError());
        $this->assertEquals($fcm->response_data, $error);
    }

    /** @test */
    public function set_send_status()
    {
        $user = $this->userBuilder()->create();
        $fcm = Fcm::factory()->create(['user_id' => $user->id]);
        $fcm->refresh();

        $this->assertFalse($fcm->isSend());
        $this->assertNull($fcm->response_data);

        $res = 'some_error';
        $fcm->setSendStatus($res);
        $fcm->refresh();

        $this->assertTrue($fcm->isSend());
        $this->assertEquals($fcm->response_data, $res);
    }
}



