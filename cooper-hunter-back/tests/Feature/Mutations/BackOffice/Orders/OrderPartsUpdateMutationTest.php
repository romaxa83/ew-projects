<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderPartsUpdateMutation;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderPartsUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_update_parts(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $categories = $this->createOrderCategories();

        $query = new GraphQLQuery(
            OrderPartsUpdateMutation::NAME,
            [
                'id' => $order->id,
                'parts' => [
                    [
                        'id' => $categories[0]->id,
                    ],
                    [
                        'id' => $categories[1]->id,
                    ],
                ]
            ],
            [
                'id',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPartsUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'parts' => [
                                [
                                    'id' => (string)$categories[0]->id,
                                    'name' => $categories[0]->translation->title,
                                    'description' => null,
                                    'quantity' => 1,
                                    'price' => null
                                ],
                                [
                                    'id' => (string)$categories[1]->id,
                                    'name' => $categories[1]->translation->title,
                                    'description' => null,
                                    'quantity' => 1,
                                    'price' => null
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . OrderPartsUpdateMutation::NAME . '.parts');
    }

    public function test_update_parts_wo_price_on_pending_paid_order(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $categories = $this->createOrderCategories();

        $query = new GraphQLQuery(
            OrderPartsUpdateMutation::NAME,
            [
                'id' => $order->id,
                'parts' => [
                    [
                        'id' => $categories[0]->id,
                    ],
                    [
                        'id' => $categories[1]->id,
                    ],
                ]
            ],
            [
                'id',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.order.order_part_price_is_required')
                        ]
                    ]
                ]
            );
    }

    public function test_update_parts_with_price_on_pending_paid_order(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $categories = $this->createOrderCategories();

        $query = new GraphQLQuery(
            OrderPartsUpdateMutation::NAME,
            [
                'id' => $order->id,
                'parts' => [
                    [
                        'id' => $categories[0]->id,
                        'price' => 100.00,
                    ],
                    [
                        'id' => $categories[1]->id,
                        'price' => 200.00
                    ],
                ]
            ],
            [
                'id',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPartsUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'parts' => [
                                [
                                    'id' => (string)$categories[0]->id,
                                    'name' => $categories[0]->translation->title,
                                    'description' => null,
                                    'quantity' => 1,
                                    'price' => 100.00
                                ],
                                [
                                    'id' => (string)$categories[1]->id,
                                    'name' => $categories[1]->translation->title,
                                    'description' => null,
                                    'quantity' => 1,
                                    'price' => 200.00
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . OrderPartsUpdateMutation::NAME . '.parts');
    }
}
