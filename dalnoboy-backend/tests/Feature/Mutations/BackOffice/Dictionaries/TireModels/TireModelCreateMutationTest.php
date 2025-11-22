<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelCreateMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireModelCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_model(): void
    {
        $tireMake = TireMake::factory()->create();

        $tireModelData = [
            'active' => true,
            'tire_make_id' => $tireMake->getKey(),
            'title' => 'test title',
        ];

        $tireModelId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelCreateMutation::NAME)
                ->args(
                    [
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'tire_make' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireModelCreateMutation::NAME => [
                            'id',
                            'active',
                            'title',
                            'tire_make' => [
                                'id',
                            ],
                        ]
                    ]
                ]
            )
            ->json('data.' . TireModelCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireModel::class,
            [
                'id' => $tireModelId,
                'active' => true,
                'tire_make_id' => $tireMake->getKey(),
                'title' => 'test title',
            ]
        );
    }

    public function test_empty_title(): void
    {
        $tireMake = TireMake::factory()->create();

        $tireModelData = [
            'active' => true,
            'tire_make_id' => $tireMake->getKey(),
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelCreateMutation::NAME)
                ->args(
                    [
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireModelInputType.title of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }

    public function test_create_tire_model_without_tire_make(): void
    {
        $tireModelData = [
            'active' => true,
            'title' => 'test title',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelCreateMutation::NAME)
                ->args(
                    [
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'tire_form' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireModelInputType.tire_make_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
