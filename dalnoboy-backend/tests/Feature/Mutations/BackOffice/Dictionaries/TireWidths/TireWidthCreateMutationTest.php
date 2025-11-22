<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireWidths;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthCreateMutation;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireWidthCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_width(): void
    {
        $tireWidthData = [
            'active' => true,
            'value' => 110,
        ];

        $tireWidthId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthCreateMutation::NAME)
                ->args(
                    [
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
            ->assertJsonStructure(
                [
                    'data' => [
                        TireWidthCreateMutation::NAME => [
                            'id',
                            'active',
                            'value',
                        ]
                    ]
                ]
            )
            ->json('data.' . TireWidthCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireWidth::class,
            [
                'id' => $tireWidthId,
                'active' => true,
                'value' => 110,
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireWidthData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthCreateMutation::NAME)
                ->args(
                    [
                        'tire_width' => $tireWidthData
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
                            'message' => 'Field TireWidthInputType.value of required type Float! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
