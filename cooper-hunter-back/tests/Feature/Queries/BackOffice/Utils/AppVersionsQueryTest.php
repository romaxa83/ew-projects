<?php

namespace Tests\Feature\Queries\BackOffice\Utils;

use App\GraphQL\Queries\BackOffice\Utilities\AppVersionsQuery;
use App\Models\Utils\Version;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppVersionsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_version_list(): void
    {
        $this->loginAsSuperAdmin();

        $version = Version::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AppVersionsQuery::NAME)
                ->select(
                    [
                        'recommended_version',
                        'required_version',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AppVersionsQuery::NAME => [
                            'recommended_version' => $version->recommended_version,
                            'required_version' => $version->required_version,
                        ]
                    ],
                ]
            );
    }
}