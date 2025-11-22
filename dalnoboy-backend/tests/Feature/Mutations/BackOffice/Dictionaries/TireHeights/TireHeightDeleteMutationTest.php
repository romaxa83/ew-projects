<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireHeights;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightDeleteMutation;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireSize;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireHeightDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_height(): void
    {
        $tireHeight = TireHeight::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireHeight->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireHeightDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireHeight::class,
            [
                'id' => $tireHeight->id,
            ]
        );
    }

    public function test_delete_tire_height_with_tire_sizes(): void
    {
        $tireHeight = TireHeight::factory()->create();
        TireSize::factory()->for($tireHeight)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireHeight->id,
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
            TireHeight::class,
            [
                'id' => $tireHeight->id,
            ]
        );
    }
}
