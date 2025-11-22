<?php

namespace Tests\Feature\Queries\FrontOffice\Projects;

use App\GraphQL\Queries\FrontOffice\Projects\UserProjectsQuery;

class UserProjectsQueryTest extends BaseProjectsQueryTest
{
    public const QUERY = UserProjectsQuery::NAME;

    public function test_get_user_project_list(): void
    {
        $this->assertMemberCanViewProjects(
            $this->loginAsUserWithRole()
        );
    }

    public function test_get_project_by_id(): void
    {
        $this->assertMemberCanViewProjectById(
            $this->loginAsUserWithRole()
        );
    }
}
