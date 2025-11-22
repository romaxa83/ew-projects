<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeDeleteMutation;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSizeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_size(): void
    {
        $tireSize = TireSize::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireSize->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireSizeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireSize::class,
            [
                'id' => $tireSize->id,
            ]
        );
    }

    public function test_delete_tire_size_with_tire_specifications(): void
    {
        $tireSize = TireSize::factory()
            ->create();
        TireSpecification::factory()
            ->for($tireSize, 'tireSize')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireSize->id,
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
            TireSize::class,
            [
                'id' => $tireSize->id,
            ]
        );
    }
}
