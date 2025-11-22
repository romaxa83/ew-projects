<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeCreateMutation;
use App\Models\Dictionaries\TireMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireMakeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_make(): void
    {
        $tireMakeData = [
            'active' => true,
            'title' => 'test title',
        ];

        $tireMakeId = $this->postGraphQLBackOffice(
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
                'title' => 'test title',
            ]
        );
    }

    public function test_empty_title(): void
    {
        $tireMakeData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
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
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireMakeInputType.title of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
