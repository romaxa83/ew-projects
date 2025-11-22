<?php


namespace Tests\Feature\Queries\BackOffice\Users;


use App\GraphQL\Queries\BackOffice\Users\UsersExportQuery;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UsersExportQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()
            ->count(5)
            ->create();

        $this->loginAsAdminWithRole();
    }

    public function test_get_download_link(): void
    {
        $link = $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersExportQuery::NAME)
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
                        UsersExportQuery::NAME => [
                            'link'
                        ]
                    ]
                ]
            )
            ->json('data.' . UsersExportQuery::NAME . '.link');

        $this
            ->get($link)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename=users.xlsx');
    }
}
