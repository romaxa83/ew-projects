<?php

namespace Tests\Unit\Services\Firebase;

use App\Events\Firebase\FcmPush;
use App\Models\Admin\Admin;
use App\Models\Notification\Fcm;
use App\Services\Firebase\FcmAction;
use App\Services\Firebase\FcmService;
use Database\Factories\Permission\RoleFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class FcmServiceTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(FcmService::class);
    }

    /** @test */
    public function success_create_from_event()
    {
        $user = $this->userBuilder()->create();

        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);
        $event = new FcmPush($user, $action);

        $this->assertNotEmpty($event->user);
        $this->assertNotEmpty($event->action);
        $this->assertNull($event->relatedModel);

        $fcm = $this->service->createFromEvent($event);

        $fcm->refresh();

        $this->assertEquals($fcm->user_id, $user->id);
        $this->assertEquals($fcm->user->id, $user->id);
        $this->assertEquals($fcm->status, Fcm::STATUS_CREATED);
        $this->assertEquals($fcm->action, FcmAction::ORDER_ACCEPT);
        $this->assertEquals($fcm->type, Fcm::TYPE_NEW);
        $this->assertNull($fcm->entity_type);
        $this->assertNull($fcm->entity_id);
        $this->assertNull($fcm->response_data);
        $this->assertEquals($fcm->send_data['title'], $action->getTitle());
        $this->assertEquals($fcm->send_data['body'], $action->getBody());
    }

    /** @test */
    public function success_create_from_event_with_related()
    {
        $user = $this->userBuilder()->create();

        $role = RoleFactory::new([
            'guard_name' => Admin::GUARD,
            'name' => \Str::random()
        ])->create();

        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);
        $event = new FcmPush($user, $action, $role);

        $this->assertNotEmpty($event->user);
        $this->assertNotEmpty($event->action);
        $this->assertNotEmpty($event->relatedModel);

        $fcm = $this->service->createFromEvent($event);

        $fcm->refresh();

        $this->assertEquals($fcm->user_id, $user->id);
        $this->assertEquals($fcm->user->id, $user->id);
        $this->assertEquals($fcm->status, Fcm::STATUS_CREATED);
        $this->assertEquals($fcm->action, FcmAction::ORDER_ACCEPT);
        $this->assertEquals($fcm->entity_type, $role::class);
        $this->assertEquals($fcm->entity_id, $role->id);
        $this->assertEquals($fcm->entity->name, $role->name);
        $this->assertNull($fcm->response_data);
        $this->assertEquals($fcm->send_data['title'], $action->getTitle());
        $this->assertEquals($fcm->send_data['body'], $action->getBody());
        $this->assertEquals($fcm->type, Fcm::TYPE_NEW);
    }
}


