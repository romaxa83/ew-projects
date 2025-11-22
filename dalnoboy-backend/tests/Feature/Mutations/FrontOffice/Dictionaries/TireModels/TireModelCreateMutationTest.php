<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\TireModels\TireModelCreateMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireModelCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_tire_model(): void
    {
        $make = TireMake::factory()->create();
        $tireModelData = [
            'title' => 'test title123',
            'tire_make_id' => $make->id,
        ];

        $tireModelId = $this->postGraphQL(
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
                'tire_make_id' => $make->id,
                'title' => 'test title123',
            ]
        );
    }

    public function test_create_same_tire_model(): void
    {
        $make = TireMake::factory()->create();
        TireModel::factory()->create(['title' => 'test title123', 'tire_make_id' => $make->id]);
        $tireModelData = [
            'title' => 'test title123',
            'tire_make_id' => $make->id,
        ];

        $this->postGraphQL(
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
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.same_entity_exists'),
                        ]
                    ]
                ]
            );
    }

    public function test_create__same_tire_model_in_offline(): void
    {
        $make = TireMake::factory()->create();
        $tireModel = TireModel::factory()
            ->create(['title' => 'test title123', 'tire_make_id' => $make->id]);
        $tireModelData = [
            'title' => 'test title123',
            'tire_make_id' => $make->id,
            'is_offline' => true,
        ];

        $tireModelId = $this->postGraphQL(
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
            ->json('data.' . TireModelCreateMutation::NAME . '.id');

        $this->assertEquals($tireModel->id, $tireModelId);
    }
}
