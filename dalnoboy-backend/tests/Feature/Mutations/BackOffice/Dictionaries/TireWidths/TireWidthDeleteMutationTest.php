<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireWidths;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthDeleteMutation;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireWidthDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_width(): void
    {
        $tireWidth = TireWidth::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireWidth->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireWidthDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireWidth::class,
            [
                'id' => $tireWidth->id,
            ]
        );
    }

    public function test_delete_tire_width_with_tire_sizes(): void
    {
        $tireWidth = TireWidth::factory()
            ->create();
        TireSize::factory()
            ->for($tireWidth)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireWidth->id,
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
            TireWidth::class,
            [
                'id' => $tireWidth->id,
            ]
        );
    }
}
