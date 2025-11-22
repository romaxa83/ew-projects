<?php

namespace Tests\Feature\Queries\FrontOffice\Counters;

use App\GraphQL\Queries\FrontOffice\Counters\ProjectCounterQuery;
use App\Models\Projects\Project;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectCounterQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ProjectCounterQuery::NAME;

    public function test_get_user_projects_count(): void
    {
        $user = $this->loginAsUserWithRole();

        Project::factory()
            ->times(5)
            ->forUser($user)
            ->create();

        Project::factory()
            ->times(5)
            ->forTechnician()
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->postGraphQL($query->getQuery())
            ->assertJsonPath('data.' . self::QUERY, 5);
    }

    public function test_get_technician_projects_count(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        Project::factory()
            ->times(5)
            ->forUser()
            ->create();

        Project::factory()
            ->times(5)
            ->forTechnician($technician)
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->postGraphQL($query->getQuery())
            ->assertJsonPath('data.' . self::QUERY, 5);
    }

    public function test_unauthorized_technician(): void
    {
        $technician = $this->loginAsTechnician();

        Project::factory()
            ->times(5)
            ->forTechnician($technician)
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->assertServerError($this->postGraphQL($query->getQuery()), 'No permission');
    }

    public function test_unauthorized_user(): void
    {
        $user = $this->loginAsUser();

        Project::factory()
            ->times(5)
            ->forUser($user)
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->assertServerError($this->postGraphQL($query->getQuery()), 'No permission');
    }
}
