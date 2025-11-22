<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationDeleteMutation;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireSpecificationDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_specification(): void
    {
        $tireSpecification = TireSpecification::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireSpecification->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireSpecificationDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireSpecification::class,
            [
                'id' => $tireSpecification->id,
            ]
        );
    }

    public function test_delete_tire_specification_type_with_tire(): void
    {
        $tireSpecification = TireSpecification::factory()->create();
        Tire::factory()->for($tireSpecification, 'specification')->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireSpecification->id,
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
            TireSpecification::class,
            [
                'id' => $tireSpecification->id,
            ]
        );
    }
}
