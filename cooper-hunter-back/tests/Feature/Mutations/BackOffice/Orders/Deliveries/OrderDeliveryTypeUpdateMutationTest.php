<?php


namespace Feature\Mutations\BackOffice\Orders\Deliveries;


use App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeUpdateMutation;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Deliveries\OrderDeliveryTypeTranslation;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDeliveryTypeUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function test_update_delivery_type(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeUpdatePermission::KEY]);

        $orderDeliveryType = OrderDeliveryType::factory()->create();

        $query = new GraphQLQuery(
            OrderDeliveryTypeUpdateMutation::NAME,
            [
                'id' => $orderDeliveryType->id,
                'translations' => [
                    [
                        'title' => 'en title',
                        'language' => 'en',
                    ],
                    [
                        'title' => 'es title',
                        'language' => 'es',
                    ]
                ],
            ],
            [
                'id',
                'translation' => [
                    'title'
                ],
                'translations' => [
                    'title'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderDeliveryTypeUpdateMutation::NAME => [
                        'id',
                        'translation' => [
                            'title'
                        ],
                        'translations' => [
                            '*' => [
                                'title'
                            ]
                        ]
                    ]
                ]
            ])
            ->json('data.' . OrderDeliveryTypeUpdateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderDeliveryTypeTranslation::class,
            [
                'row_id' => $orderDeliveryType->id,
                'language' => 'en'
            ]
        );

        $this->assertDatabaseHas(
            OrderDeliveryTypeTranslation::class,
            [
                'row_id' => $orderDeliveryType->id,
                'language' => 'es'
            ]
        );
    }

}
