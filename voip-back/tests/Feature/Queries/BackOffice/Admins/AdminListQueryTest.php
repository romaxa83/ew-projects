<?php

namespace Tests\Feature\Queries\BackOffice\Admins;

use App\GraphQL\Queries\BackOffice\Admins\AdminsListQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Permissions\RoleBuilder;
use Tests\TestCase;

class AdminListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AdminsListQuery::NAME;

    protected AdminBuilder $adminBuilder;
    protected RoleBuilder $roleBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminBuilder = resolve(AdminBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
    }

    /** @test */
    public function success_list_as_super_admin(): void
    {
        $a_1 = $this->adminBuilder->setData(['name' => "woo"])->create();
        $this->loginAsSuperAdmin($a_1);

        $role_1 = $this->roleBuilder->create();
        $role_2 = $this->roleBuilder->create();

        $a_2 = $this->adminBuilder->setRole($role_1)->setData(['name' => "zen"])->create();
        $a_3 = $this->adminBuilder->setRole($role_1)->setData(['name' => "alan"])->create();
        $a_4 = $this->adminBuilder->setRole($role_2)->setData(['name' => "duck"])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $a_3->id],
                        ['id' => $a_4->id],
                        ['id' => $a_2->id],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'. self::QUERY)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            query {
                %s {
                    id
                    name
                    role
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertUnauthorized($res);
    }
}
