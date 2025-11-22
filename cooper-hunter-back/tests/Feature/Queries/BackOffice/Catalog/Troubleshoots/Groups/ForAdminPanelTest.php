<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Troubleshoots\Groups;

use App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Groups;
use App\Models\Admins\Admin;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group as GroupPerm;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\Troubleshoots\GroupBuilder;
use Tests\Builders\Catalog\Troubleshoots\TroubleshootBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = Groups\GroupsQuery::NAME;
    protected GroupBuilder $builder;
    protected TroubleshootBuilder $builderTroubleshoot;

    public function test_get_groups_list(): void
    {
        $this->loginByAdminManager([GroupPerm\ListPermission::KEY]);

        Group::factory()
            ->times(5)
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [],
            [
                'id'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonCount(5, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                            ]
                        ],
                    ],
                ]
            );
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }
}
