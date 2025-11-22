<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireWidths;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthToggleActiveMutation;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireWidthToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_width(): void
    {
        $tireWidth = TireWidth::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireWidthToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireWidth->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireWidthToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireWidth->refresh();
        $this->assertFalse((bool)$tireWidth->active);
    }
}
