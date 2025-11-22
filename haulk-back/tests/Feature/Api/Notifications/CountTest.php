<?php

namespace Tests\Feature\Api\Notifications;

use App\Enums\Notifications\NotificationStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Notifications\NotificationBuilder;
use Tests\TestCase;

class CountTest extends TestCase
{
    use DatabaseTransactions;

    protected NotificationBuilder $notificationBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationBuilder = resolve(NotificationBuilder::class);
    }

    /** @test */
    public function success_count_new()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->notificationBuilder->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->status(NotificationStatus::READ())->create();

        $this->getJson(route('api-notifications-count', [
            'status' => NotificationStatus::NEW
        ]))
            ->assertJson([
                'data' => 3
            ])
        ;
    }
}


