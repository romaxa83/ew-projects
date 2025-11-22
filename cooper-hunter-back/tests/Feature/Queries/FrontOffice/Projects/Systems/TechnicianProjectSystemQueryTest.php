<?php

namespace Tests\Feature\Queries\FrontOffice\Projects\Systems;

use App\GraphQL\Queries\FrontOffice\Projects\Systems\TechnicianProjectSystemQuery;
use App\Models\Projects\System;

class TechnicianProjectSystemQueryTest extends BaseProjectSystemQueryTest
{
    public const QUERY = TechnicianProjectSystemQuery::NAME;

    public function test_technician_can_view_own_system(): void
    {
        $system = $this->createSystemForMember(
            $this->loginAsTechnicianWithRole()
        );

        $query = $this->getGraphQLQuery(
            [
                'id' => $system->id
            ]
        );

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_technician_can_not_view_foreign_system(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = $this->getGraphQLQuery(
            [
                'id' => System::factory()->create()->id
            ]
        );

        $this->assertServerError($this->postGraphQL($query), 'validation');
    }
}
