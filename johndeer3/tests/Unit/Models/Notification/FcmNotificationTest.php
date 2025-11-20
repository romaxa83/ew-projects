<?php

namespace Tests\Unit\Models\Notification;

use App\Models\Notification\FcmNotification;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class FcmNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function check_has_error(): void
    {
        /** @var $model FcmNotification */
        $model = FcmNotification::factory()->create([
            "status" => FcmNotification::STATUS_HAS_ERROR,
        ]);

        $this->assertTrue($model->hasError());
    }

    /** @test */
    public function check_is_send(): void
    {
        /** @var $model FcmNotification */
        $model = FcmNotification::factory()->create([
            "status" => FcmNotification::STATUS_SEND,
        ]);

        $this->assertTrue($model->isSend());
    }

    /** @test */
    public function check_entity_relations(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        /** @var $model FcmNotification */
        $model = FcmNotification::factory()->create([
            "entity_type" => User::class,
            "entity_id" => $user->id,
        ]);

        $this->assertTrue($model->entity instanceof User);
        $this->assertEquals($model->entity->id, $user->id);
    }
}


