<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupToggleActiveMutation;
use App\Models\Catalog\Troubleshoots;
use App\Permissions\Catalog\Troubleshoots\Group;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Group\UpdatePermission::KEY]);
    }

    public function test_success(): void
    {
        $group = Troubleshoots\Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootGroupToggleActiveMutation::NAME)
                ->args([
                    'id' => $group->id,
                ])
                ->select([
                    'id',
                    'active'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    TroubleshootGroupToggleActiveMutation::NAME => [
                        'id' => $group->id,
                        'active' => false
                    ]
                ]
            ]);
    }
}


