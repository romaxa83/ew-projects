<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeDeleteMutation;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireTypeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_type(): void
    {
        $tireType = TireType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireType->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireTypeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireType::class,
            [
                'id' => $tireType->id,
            ]
        );
    }

    public function test_delete_tire_type_with_tire_specifications(): void
    {
        $tireType = TireType::factory()
            ->create();
        TireSpecification::factory()
            ->for($tireType, 'tireType')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireType->id,
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
            TireType::class,
            [
                'id' => $tireType->id,
            ]
        );
    }
}
