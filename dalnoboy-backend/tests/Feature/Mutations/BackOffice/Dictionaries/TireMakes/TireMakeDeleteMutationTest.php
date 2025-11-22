<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeDeleteMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSpecification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireMakeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_make(): void
    {
        $tireMake = TireMake::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireMake->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireMakeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireMake::class,
            [
                'id' => $tireMake->id,
            ]
        );
    }

    public function test_delete_tire_make_with_tire_models(): void
    {
        $tireMake = TireMake::factory()->create();
        TireModel::factory()->for($tireMake)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireMake->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.has_related_entities'),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            TireMake::class,
            [
                'id' => $tireMake->id,
            ]
        );
    }

    public function test_delete_tire_make_with_tire_specifications(): void
    {
        $tireMake = TireMake::factory()
            ->create();
        TireSpecification::factory()
            ->for($tireMake, 'tireMake')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireMake->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.has_related_entities'),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            TireMake::class,
            [
                'id' => $tireMake->id,
            ]
        );
    }
}
