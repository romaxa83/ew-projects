<?php

namespace Tests\Feature\Queries\BackOffice\Auth\Admin;

use App\GraphQL\Queries\BackOffice\Auth\Admin\AdminProfileQuery;
use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class AdminProfileQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AdminProfileQuery::NAME;

    protected AdminBuilder $adminBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminBuilder = resolve(AdminBuilder::class);
    }

    /** @test */
    public function get_profile(): void
    {
        $model = $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $model->id,
                        'email' => $model->email->getValue(),
                        'name' => $model->getName(),
                    ]
                ]
            ])
            ->assertJsonCount(
                Permission::query()->where('guard_name', Admin::GUARD)->count(),
                'data.' . self::QUERY . '.permissions'
            )
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                    email
                    name
                    permissions {
                        name
                    }
                }
            }',
            self::QUERY
        );
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
