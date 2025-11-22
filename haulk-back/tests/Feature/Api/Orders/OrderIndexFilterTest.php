<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\TestCase;

class OrderIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    public function test_it_show_orders()
    {
        $order1 = Order::factory()
            ->create();
        $order2 = Order::factory()
            ->newStatus()
            ->create();
        $order3 = Order::factory()
            ->assignedStatus()
            ->create();
        $order4 = Order::factory()
            ->pickedUpStatus()
            ->create();
        $order5 = Order::factory()
            ->deliveredStatus()
            ->create();
        $order6 = Order::factory()
            ->deletedStatus()
            ->create();
        $this->makeDocuments();
        $this->loginAsCarrierDispatcher();

        $this->getJson(
            route(
                'orders.index',
                [
                    'state' => [
                        Order::CALCULATED_STATUS_NEW
                    ],
                ]
            )
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'id' => $order2->id
                        ]
                    ]
                ]
            );

        $this->getJson(
            route(
                'orders.index',
                [
                    'state' => [
                        Order::CALCULATED_STATUS_ASSIGNED,
                        Order::CALCULATED_STATUS_PICKED_UP,
                    ],
                ]
            )
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'id' => $order3->id
                        ],
                        [
                            'id' => $order4->id
                        ]
                    ]
                ]
            );

        $this->getJson(
            route(
                'orders.index',
                [
                    'state' => [
                        Order::CALCULATED_STATUS_DELIVERED,
                        Order::CALCULATED_STATUS_DELETED,
                    ],
                ]
            )
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'id' => $order5->id
                        ],
                        [
                            'id' => $order6->id
                        ]
                    ]
                ]
            );
    }
}
