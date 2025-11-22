<?php

namespace Tests\Feature\Queries\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Types\Permissions;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class GetOneAdminTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        Admin::factory()->count(3)->create();
        $builder = $this->adminBuilder();

        $mainAdmin = $builder->createRoleWithPerms([Permissions::ADMIN_GET])->create();
        $this->loginAsAdmin($mainAdmin);

        $someAdmin = $builder->setEmail('some@test.com')->createRoleWithPerm(Permissions::ADMIN_GET)->create();

        $this->assertNotEquals($mainAdmin->id, $someAdmin->id);

        $response = $this->graphQL($this->getQueryStr($someAdmin->id))
            ->assertOk();

        $responseData = $response->json('data.admin');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('lastLoginAt', $responseData);
        $this->assertArrayHasKey('name', $responseData['role']);
        $this->assertArrayHasKey('current', $responseData['role']);
        $this->assertArrayHasKey('name', $responseData['role']['current']);
        $this->assertArrayHasKey('lang', $responseData['role']['current']);
        $this->assertArrayHasKey('translations', $responseData['role']);
        $this->assertArrayHasKey('permissions', $responseData['role']);
        $this->assertCount(1, $responseData['role']['permissions']);
        $this->assertCount(2, $responseData['role']['translations']);
        $this->assertArrayHasKey('name', $responseData['role']['permissions'][0]);
        $this->assertArrayHasKey('name', $responseData['role']['translations'][0]);

        $this->assertEquals($responseData['id'], $someAdmin->id);
        $this->assertEquals($responseData['email'], $someAdmin->email);
        $this->assertEquals($responseData['name'], $someAdmin->name);
        $this->assertEquals($responseData['status'], $this->admin_status_active);
        $this->assertEquals($responseData['role']['name'], $someAdmin->role->name);
        $this->assertEquals($responseData['role']['current']['lang'], $someAdmin->lang);
        $this->assertEquals($responseData['role']['current']['name'], $someAdmin->role->current->name);
        $this->assertNull($responseData['lastLoginAt']);
        //@todo test при согласовании формата даты, сделать проверку на нее
    }

    /** @test */
    public function get_success_without_role()
    {
        $builder = $this->adminBuilder();

        $mainAdmin = $builder->createRoleWithPerms([Permissions::ADMIN_GET])->create();
        $this->loginAsAdmin($mainAdmin);

        $someAdmin = Admin::factory()->new(['email' => 'some@email.com'])->create();

        $this->assertNotEquals($mainAdmin->id, $someAdmin->id);

        $response = $this->graphQL($this->getQueryStr($someAdmin->id))
            ->assertOk();

        $responseData = $response->json('data.admin');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('role', $responseData);

        $this->assertNull($responseData['role']);
    }

    /** @test */
    public function check_last_login()
    {
        $builder = $this->adminBuilder();

        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_GET])->create();
        $this->loginAsAdmin($admin);

        $dateLastLogin = Carbon::now()->subHour();

        $someAdmin = $builder->setEmail('some@test.com')
            ->withLastLogins($dateLastLogin)
            ->createRoleWithPerm(Permissions::ADMIN_GET)
            ->create();

        $this->assertNotEmpty($someAdmin->logins);
        $this->assertGreaterThan('1',$someAdmin->logins()->count());

        $response = $this->graphQL($this->getQueryStr($someAdmin->id))
            ->assertOk();

        $responseData = $response->json('data.admin');

        $this->assertArrayHasKey('lastLoginAt', $responseData);
        $this->assertEquals($dateLastLogin, $responseData['lastLoginAt']);

    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_GET])
            ->create();

        $someAdmin = Admin::factory()->new(['email' => 'some@email.com'])->create();

        $response = $this->graphQL($this->getQueryStr($someAdmin->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = Admin::factory()->new(['email' => 'some@email.com'])->create();

        $response = $this->graphQL($this->getQueryStr($someAdmin->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr($id): string
    {
        return  sprintf('{
            admin (id: %d){
                id
                email
                status
                name
                lang
                createdAt
                lastLoginAt
                role {
                    name
                    current {
                        lang
                        name
                    }
                    translations {
                        name
                    }
                    permissions {
                        name
                    }
                }
               }
            }',
            $id
        );
    }
}
