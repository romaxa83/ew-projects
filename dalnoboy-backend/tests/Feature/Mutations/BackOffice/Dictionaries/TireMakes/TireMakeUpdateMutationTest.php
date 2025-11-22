<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeUpdateMutation;
use App\Models\Dictionaries\TireMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireMakeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_make(): void
    {
        $tireMake = TireMake::factory()->create();

        $tireMakeData = [
            'active' => true,
            'title' => 'test title 2',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireMake->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            TireMake::class,
            [
                'id' => $tireMake->id,
                'active' => true,
                'title' => 'test title 2',
            ]
        );
    }

    public function test_empty_title(): void
    {
        $tireMake = TireMake::factory()->create();

        $tireMakeData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireMake->id,
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
                            'message' => 'Field TireMakeInputType.title of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }

}
