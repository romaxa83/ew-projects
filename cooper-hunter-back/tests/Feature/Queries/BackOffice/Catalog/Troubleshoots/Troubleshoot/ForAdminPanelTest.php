<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootQuery;
use App\Models\Admins\Admin;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot\ListPermission;
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

    public const QUERY = TroubleshootQuery::NAME;
    protected GroupBuilder $builderGroup;
    protected TroubleshootBuilder $builder;

    public function test_get_troubleshoots_for_product(): void
    {
        $this->loginByAdminManager([ListPermission::KEY]);

        $product = Product::factory()->create();

        $t = Troubleshoot::factory()
            ->times(5)
            ->for(
                Group::factory()
                    ->hasAttached(
                    factory: $product,
                    relationship: 'products'
                )->create()
            )
            ->create();

        Troubleshoot::factory()
            ->times(5)
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'product_id' => $product->id,
            ],
            [
                'id',
                'name',
            ],
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonCount(5, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ]
            );

        $this->assertDatabaseCount(Troubleshoot::TABLE, 10);
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }
}
