<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelUpdateMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireModelUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_model(): void
    {
        $tireModel = TireModel::factory()->create();

        $tireMake = TireMake::factory()->create();
        $tireModelData = [
            'active' => true,
            'tire_make_id' => $tireMake->getKey(),
            'title' => 'test title 2',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireModel->id,
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            TireModel::class,
            [
                'id' => $tireModel->getKey(),
                'active' => true,
                'tire_make_id' => $tireMake->getKey(),
                'title' => 'test title 2',
            ]
        );
    }

    public function test_empty_title(): void
    {
        $tireModel = TireModel::factory()->create();

        $tireMake = TireMake::factory()->create();

        $tireModelData = [
            'active' => true,
            'tire_make_id' => $tireMake->getKey(),
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireModel->id,
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
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

    public function test_update_tire_model_without_tire_form(): void
    {
        $tireModel = TireModel::factory()->create();

        $tireModelData = [
            'active' => true,
            'title' => 'test title 2',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireModel->id,
                        'tire_model' => $tireModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
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
