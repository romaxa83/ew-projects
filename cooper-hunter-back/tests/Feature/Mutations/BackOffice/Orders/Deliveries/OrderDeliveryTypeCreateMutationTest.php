<?php


namespace Feature\Mutations\BackOffice\Orders\Deliveries;


use App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeCreateMutation;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeCreatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDeliveryTypeCreateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function test_create_delivery_type(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeCreatePermission::KEY]);

        $query = new GraphQLQuery(
            OrderDeliveryTypeCreateMutation::NAME,
            [
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

        $id = $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderDeliveryTypeCreateMutation::NAME => [
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
            ->json('data.' . OrderDeliveryTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderDeliveryType::class,
            [
                'id' => $id
            ]
        );
    }

    public function test_try_create_delivery_type_wo_one_language(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeCreatePermission::KEY]);

        $query = new GraphQLQuery(
            OrderDeliveryTypeCreateMutation::NAME,
            [
                'translations' => [
                    [
                        'title' => 'en title',
                        'language' => 'en',
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
            ->assertJsonPath('errors.0.message', 'validation');
    }

}
