<?php

namespace Tests\Unit\Listeners\Firebase;

use App\Events\Firebase\FcmPush;
use App\Listeners\Firebase\FcmPushListeners;
use App\Services\Firebase\FcmAction;
use App\Services\Firebase\Sender\CustomFirebaseSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class FcmPushListenersTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function user_have_fcm_token()
    {
        \Config::set('firebase.enable_firebase', true);

        $response = 'success';
        $sender = $this->createStub(CustomFirebaseSender::class);
        $sender->method('send')->willReturn($response);

        $user = $this->userBuilder()->setFcmToken('some_token')->create();
        $user->refresh();
        $this->assertEmpty($user->fcmNotifications);
        $this->assertFalse($user->has_new_notifications);

        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);
        $event = new FcmPush($user, $action);

        $listener = new FcmPushListeners($sender);
        $listener->handle($event);

        $user->refresh();

        $this->assertNotEmpty($user->fcmNotifications);
        $this->assertTrue($user->fcmNotifications[0]->isSend());
        $this->assertEquals($user->fcmNotifications[0]->response_data, $response);
        $this->assertTrue($user->has_new_notifications);
    }

    /** @test */
    public function user_not_have_fcm_token()
    {
        \Config::set('firebase.enable_firebase', true);

        $sender = $this->createStub(CustomFirebaseSender::class);

        $user = $this->userBuilder()->create();

        $user->refresh();
        $this->assertEmpty($user->fcmNotifications);
        $this->assertFalse($user->has_new_notifications);

        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);
        $event = new FcmPush($user, $action);

        $listener = new FcmPushListeners($sender);
        $listener->handle($event);

        $user->refresh();
        $this->assertNotEmpty($user->fcmNotifications);
        $this->assertTrue($user->fcmNotifications[0]->hasError());
        $this->assertNotNull($user->fcmNotifications[0]->response_data);
        $this->assertFalse($user->has_new_notifications);
    }

    /** @test */
    public function user_not_have_fcm_token_enable()
    {
        \Config::set('firebase.enable_firebase', false);

        $sender = $this->createStub(CustomFirebaseSender::class);

        $user = $this->userBuilder()->create();

        $user->refresh();
        $this->assertEmpty($user->fcmNotifications);

        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);
        $event = new FcmPush($user, $action);

        $listener = new FcmPushListeners($sender);
        $listener->handle($event);

        $user->refresh();
        $this->assertEmpty($user->fcmNotifications);
    }
}
