<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonToggleActiveMutation;
use App\Models\Dictionaries\InspectionReason;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionReasonToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_inspection_reason(): void
    {
        $inspectionReason = InspectionReason::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$inspectionReason->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        InspectionReasonToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $inspectionReason->refresh();
        $this->assertFalse((bool) $inspectionReason->active);
    }
}
