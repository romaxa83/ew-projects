<?php

namespace Tests\Feature\Queries\FrontOffice\Projects\Systems;

use App\GraphQL\Queries\FrontOffice\Projects\Systems\UserProjectSystemQuery;
use App\Models\Projects\System;

class UserProjectSystemQueryTest extends BaseProjectSystemQueryTest
{
    public const QUERY = UserProjectSystemQuery::NAME;

    public function test_user_can_view_own_system(): void
    {
        $system = $this->createSystemForMember(
            $this->loginAsUserWithRole()
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

    public function test_user_can_not_view_foreign_system(): void
    {
        $this->loginAsUserWithRole();

        $query = $this->getGraphQLQuery(
            [
                'id' => System::factory()->create()->id
            ]
        );

        $this->assertServerError($this->postGraphQL($query), 'validation');
    }
}
