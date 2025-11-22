<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonDeleteMutation;
use App\Models\Dictionaries\InspectionReason;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionReasonDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_inspection_reason(): void
    {
        $inspectionReason = InspectionReason::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $inspectionReason->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        InspectionReasonDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            InspectionReason::class,
            [
                'id' => $inspectionReason->id,
            ]
        );
    }
}
