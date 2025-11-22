<?php

namespace Tests\Feature\Queries\FrontOffice\Projects;

use App\GraphQL\Queries\FrontOffice\Projects\TechnicianProjectsQuery;

class TechnicianProjectsQueryTest extends BaseProjectsQueryTest
{
    public const QUERY = TechnicianProjectsQuery::NAME;

    public function test_get_user_project_list(): void
    {
        $this->assertMemberCanViewProjects(
            $this->loginAsTechnicianWithRole()
        );
    }

    public function test_get_project_by_id(): void
    {
        $this->assertMemberCanViewProjectById(
            $this->loginAsTechnicianWithRole()
        );
    }
}
