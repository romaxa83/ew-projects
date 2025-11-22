<?php

namespace Tests\Feature\Api\Notifications;

use App\Models\Notifications\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Notifications\NotificationBuilder;
use Tests\TestCase;

class ReadTest extends TestCase
{
    use DatabaseTransactions;

    protected NotificationBuilder $notificationBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationBuilder = resolve(NotificationBuilder::class);
    }

    /** @test */
    public function success_update()
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Notification */
        $model = $this->notificationBuilder->create();

        $this->assertTrue($model->status->isNew());

        $this->putJson(route('api-notifications-read'), [
            'id' => [$model->id]
        ])
            ->assertJson([
                'data' => true
            ])
        ;

        $model->refresh();

        $this->assertTrue($model->status->isRead());
    }

    /** @test */
    public function success_update_many()
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Notification */
        $model = $this->notificationBuilder->create();
        $model_1 = $this->notificationBuilder->create();

        $this->assertTrue($model->status->isNew());

        $this->putJson(route('api-notifications-read'), [
            'id' => [$model->id, $model_1->id]
        ])
            ->assertJson([
                'data' => true
            ])
        ;

        $model->refresh();
        $model_1->refresh();

        $this->assertTrue($model->status->isRead());
        $this->assertTrue($model_1->status->isRead());
    }

    /** @test */
    public function fail_return_false()
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Notification */
        $model = $this->notificationBuilder->create();

        $this->assertTrue($model->status->isNew());

        $this->putJson(route('api-notifications-read'), [
            'id' => [9999]
        ])
            ->assertJson([
                'data' => false
            ])
        ;

        $model->refresh();

        $this->assertTrue($model->status->isNew());
    }
}


