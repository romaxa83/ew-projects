<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsForCredentialsQuery;
use App\Models\Commercial\CommercialProject;
use Carbon\Carbon;
use Tests\Feature\Queries\Common\Commercial\AbstractCommercialProjectsQueryTest;

class CommercialProjectsForCredentialsQueryTest extends AbstractCommercialProjectsQueryTest
{
    public const QUERY = CommercialProjectsForCredentialsQuery::NAME;

    public function test_get_list(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        CommercialProject::factory()
            ->times(5)
            ->for($technician, 'member')
            ->create();

        CommercialProject::factory()
            ->for($technician, 'member')
            ->withCode()
            ->create();

        $query = $this->getQuery(self::QUERY, forPaginate: false);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY);

        Carbon::setTestNow(now()->addMonth()->addDay());

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(0, 'data.' . self::QUERY);
    }
}
