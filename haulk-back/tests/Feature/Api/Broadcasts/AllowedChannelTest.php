<?php

namespace Tests\Feature\Api\Broadcasts;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AllowedChannelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_get_unauthenticated_error_for_not_auth_user(): void
    {
        $this->getJson(route('broadcasts.channels'))
            ->assertUnauthorized();
    }

    public function test_it_get_super_admin_broadcast_channels(): void
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('broadcasts.channels'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'name' => 'alerts.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'alerts.company',
                            ]
                        ],
                        [
                            'name' => 'alerts.' . $this->getDefaultCarrierCompany()->id . '.user.' . $user->id,
                            'events' => [
                                'alerts.user',
                            ]
                        ],
                        [
                            'name' => 'offers.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'order.offer.new',
                                'order.offer.release',
                                'order.offer.taken',
                            ]
                        ],
                        [
                            'name' => 'orders.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'order.create',
                                'order.update',
                            ]
                        ],
                    ]
                ]
            );
    }

    public function test_it_get_dispatcher_broadcast_channels(): void
    {
        $user = $this->loginAsCarrierDispatcher();

        $this->getJson(route('broadcasts.channels'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'name' => 'alerts.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'alerts.company',
                            ]
                        ],
                        [
                            'name' => 'alerts.' . $this->getDefaultCarrierCompany()->id . '.user.' . $user->id,
                            'events' => [
                                'alerts.user',
                            ]
                        ],
                        [
                            'name' => 'offers.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'order.offer.new',
                                'order.offer.release',
                                'order.offer.taken',
                            ]
                        ],
                        [
                            'name' => 'orders.' . $this->getDefaultCarrierCompany()->id,
                            'events' => [
                                'order.create',
                                'order.update',
                            ]
                        ],
                    ]
                ]
            );
    }

    public function test_it_get_driver_broadcast_channels(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('broadcasts.channels'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                    ]
                ]
            );
    }
}
