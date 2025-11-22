<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireWidths;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthUpdateMutation;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireWidthUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_width(): void
    {
        $tireWidth = TireWidth::factory()
            ->create();

        $tireWidthData = [
            'active' => true,
            'value' => 100,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireWidth->id,
                        'tire_width' => $tireWidthData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'value',
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            TireWidth::class,
            [
                'id' => $tireWidth->id,
                'active' => true,
                'value' => 100,
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireWidth = TireWidth::factory()
            ->create();

        $tireWidthData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireWidth->id,
                        'tire_width' => $tireWidthData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'value',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireWidthInputType.value of required type Float! was not provided.'
                        ]
                    ]
                ]
            );
    }

}
