<?php


namespace Tests\Feature\Queries\BackOffice\Branches;


use App\GraphQL\Queries\BackOffice\Branches\BranchesExportQuery;
use App\Models\Branches\Branch;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BranchesExportQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Branch::factory()
            ->count(5)
            ->create();

        $this->loginAsAdminWithRole();
    }

    public function test_get_download_link(): void
    {
        $link = $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesExportQuery::NAME)
                ->select(
                    [
                        'link'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BranchesExportQuery::NAME => [
                            'link'
                        ]
                    ]
                ]
            )
            ->json('data.' . BranchesExportQuery::NAME . '.link');

        $this
            ->get($link)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename=branches.xlsx');
    }
}
