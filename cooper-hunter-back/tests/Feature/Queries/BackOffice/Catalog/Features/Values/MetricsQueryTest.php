<?php

namespace Feature\Queries\BackOffice\Catalog\Features\Values;

use App\GraphQL\Queries\BackOffice\Catalog\Features\Values;
use App\Permissions\Catalog\Features\Values as ValuePerm;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class MetricsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    /** @test */
    public function list_models(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(Values\MetricsQuery::NAME)
                ->select(
                    [
                        'id',
                        'name'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        Values\MetricsQuery::NAME => [
                            '*' => [
                                'id',
                                'name'
                            ]
                        ]
                    ]
                ]
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([ValuePerm\ListPermission::KEY]);
    }
}

