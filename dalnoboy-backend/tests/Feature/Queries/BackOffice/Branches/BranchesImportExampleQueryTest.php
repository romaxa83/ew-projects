<?php


namespace Tests\Feature\Queries\BackOffice\Branches;


use App\GraphQL\Queries\BackOffice\Branches\BranchesImportExampleQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BranchesImportExampleQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_get_download_link(): void
    {
        $link = $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesImportExampleQuery::NAME)
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
                        BranchesImportExampleQuery::NAME => [
                            'link'
                        ]
                    ]
                ]
            )
            ->json('data.' . BranchesImportExampleQuery::NAME . '.link');

        $this
            ->get($link)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename=import_example.xlsx');
    }
}
