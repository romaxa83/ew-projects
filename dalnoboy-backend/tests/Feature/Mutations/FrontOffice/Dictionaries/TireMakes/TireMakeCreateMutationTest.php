<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\TireMakes\TireMakeCreateMutation;
use App\Models\Dictionaries\TireMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireMakeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_tire_make(): void
    {
        $tireMakeData = [
            'title' => 'test title123',
        ];

        $tireMakeId = $this->postGraphQL(
            GraphQLQuery::mutation(TireMakeCreateMutation::NAME)
                ->args(
                    [
                        'tire_make' => $tireMakeData
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
                        TireMakeCreateMutation::NAME => [
                            'id',
                            'active',
                            'title',
                        ]
                    ]
                ]
            )
            ->json('data.' . TireMakeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireMake::class,
            [
                'id' => $tireMakeId,
                'active' => true,
                'title' => 'test title123',
            ]
        );
    }

    public function test_create_same_tire_make(): void
    {
        TireMake::factory()->create(['title' => 'test title123']);
        $tireMakeData = [
            'title' => 'test title123',
        ];

        $this->postGraphQL(
            GraphQLQuery::mutation(TireMakeCreateMutation::NAME)
                ->args(
                    [
                        'tire_make' => $tireMakeData
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

    public function test_create__same_tire_make_in_offline(): void
    {
        $tireMake = TireMake::factory()->create(['title' => 'test title123']);
        $tireMakeData = [
            'title' => 'test title123',
            'is_offline' => true,
        ];

        $tireMakeId = $this->postGraphQL(
            GraphQLQuery::mutation(TireMakeCreateMutation::NAME)
                ->args(
                    [
                        'tire_make' => $tireMakeData
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
            ->json('data.' . TireMakeCreateMutation::NAME . '.id');

        $this->assertEquals($tireMake->id, $tireMakeId);
    }
}
