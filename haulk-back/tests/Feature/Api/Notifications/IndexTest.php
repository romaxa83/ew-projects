<?php

namespace Tests\Feature\Api\Notifications;

use App\Enums\Notifications\NotificationStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Notifications\NotificationBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected NotificationBuilder $notificationBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationBuilder = resolve(NotificationBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->notificationBuilder->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->create();

        $this->getJson(route('notifications'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'message',
                        'status',
                        'type',
                        'place',
                        'read_at',
                        'created_at',
                    ],
                ],
            ])
            ->assertJson([
                'meta' => [
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_page()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->notificationBuilder->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->create();

        $this->getJson(route('api-notifications', [
            'page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'to' => null,
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_per_page()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->notificationBuilder->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->create();

        $this->getJson(route('api-notifications', [
            'per_page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'per_page' => 2,
                    'to' => 2,
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_empty()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('api-notifications'))
            ->assertJson([
                'meta' => [
                    'total' => 0
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->notificationBuilder->status(NotificationStatus::READ())->create();
        $this->notificationBuilder->create();
        $this->notificationBuilder->create();

        $this->getJson(route('api-notifications', [
            'status' => NotificationStatus::READ
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 1
                ]
            ])
        ;
    }

}

