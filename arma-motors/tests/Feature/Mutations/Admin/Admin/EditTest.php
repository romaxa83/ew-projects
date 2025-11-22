<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\Department;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class EditTest extends TestCase
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
    public function success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role1 = $builder->setRoleName('role_1')->createRole();
        $role2 = $builder->setRoleName('role_2')->createRole();

        $service = Service::find(1);

        $someAdmin = $builder->setEmail('some@admin.com')
            ->setDepartmentType(Department::TYPE_SERVICE)
            ->setService($service->id)
            ->attachRole($role1)
            ->create();

        $anotherService = Service::find(2);

        $data = [
            'id' => $someAdmin->id,
            'name' => 'new_name',
            'email' => 'new_email@test.com',
            'phone' => '38444444444444',
            'roleId' => $role2->id,
            'departmentType' => $this->department_type_body,
            'serviceId' => $anotherService->id
        ];

        $this->assertNotEquals($someAdmin->name, $data['name']);
        $this->assertNotEquals($someAdmin->email, $data['email']);
        $this->assertNotEquals($someAdmin->phone, $data['phone']);
        $this->assertNotEquals($someAdmin->role->id, $data['roleId']);
        $this->assertNotEquals($someAdmin->service_id, $data['serviceId']);

        $this->assertTrue($someAdmin->hasDepartment());
        $this->assertTrue($someAdmin->isServiceDepartment());
        $this->assertFalse($someAdmin->isBodyDepartment());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.adminEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('departmentType', $responseData);
        $this->assertArrayHasKey('name', $responseData['role']);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);

        $this->assertEquals($someAdmin->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['email'], $data['email']);
        $this->assertEquals($responseData['phone'], $data['phone']);
        $this->assertEquals($responseData['role']['name'], $role2->name);

        $someAdmin->refresh();
        $this->assertEquals($someAdmin->role->id, $role2->id);
        $this->assertEquals($someAdmin->name, $data['name']);
        $this->assertEquals($someAdmin->email, $data['email']);
        $this->assertEquals($someAdmin->phone, $data['phone']);
        $this->assertEquals($someAdmin->service_id, $data['serviceId']);

        $this->assertTrue($someAdmin->hasDepartment());
        $this->assertFalse($someAdmin->isServiceDepartment());
        $this->assertTrue($someAdmin->isBodyDepartment());
    }

    /** @test */
    public function success_only_dealership()
    {
        $dealership1 = Dealership::query()->where('id', 1)->first();
        $dealership2 = Dealership::query()->where('id', 2)->first();

        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')
            ->withDealership($dealership1)
            ->create();

        $data = [
            'id' => $someAdmin->id,
            'dealershipId' => $dealership2->id
        ];

        $someAdmin->refresh();

        $this->assertEquals($someAdmin->dealership->id, $dealership1->id);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyDealership($data)])
            ->assertOk();

        $responseData = $response->json('data.adminEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('dealership', $responseData);
        $this->assertArrayHasKey('id', $responseData['dealership']);
        $this->assertArrayHasKey('current', $responseData['dealership']);
        $this->assertArrayHasKey('name', $responseData['dealership']['current']);

        $this->assertEquals($someAdmin->id, $responseData['id']);
        $this->assertEquals($responseData['dealership']['id'], $dealership2->id);

        $someAdmin->refresh();

        $this->assertEquals($someAdmin->dealership->id, $dealership2->id);
    }

    // не присланные поля не будут изменены
    /** @test */
    public function success_empty_field()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role1 = $builder->createRole();

        $someAdmin = $builder->setEmail('some@admin.com')->attachRole($role1)->create();

        $data = [
            'id' => $someAdmin->id
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutField($data)]);

        $responseData = $response->json('data.adminEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('name', $responseData['role']);

        $this->assertEquals($someAdmin->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $someAdmin->name);
        $this->assertEquals($responseData['email'], $someAdmin->email);
        $this->assertEquals($responseData['phone'], $someAdmin->phone);
        $this->assertEquals($responseData['role']['name'], $role1->name);

        $someAdmin->refresh();
        $this->assertEquals($someAdmin->role->id, $role1->id);
    }

    // присылаем не все поля для редактирования
    /** @test */
    public function success_not_all_field()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role1 = $builder->createRole();

        $someAdmin = $builder->setEmail('some@admin.com')->attachRole($role1)->create();

        $data = [
            'id' => $someAdmin->id,
            'email' => 'new_email@test.com',
        ];

        $this->assertNotEquals($someAdmin->email, $data['email']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrFieldOnlyEmail($data)]);

        $responseData = $response->json('data.adminEdit');
        $someAdmin->refresh();

        $this->assertEquals($someAdmin->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $someAdmin->name);
        $this->assertEquals($responseData['email'], $someAdmin->email);
        $this->assertEquals($data['email'], $someAdmin->email);
        $this->assertEquals($responseData['phone'], $someAdmin->phone);
    }

    /** @test */
    public function fail_not_found_admin()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => "22"
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutField($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();

        $data = [
            'id' => "22"
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutField($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => "22"
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutField($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                adminEdit(input:{
                    id: "%s",
                    name: "%s",
                    email: "%s",
                    phone: "%s",
                    roleId: "%s",
                    departmentType: %s,
                    serviceId: %s
                }) {
                    id
                    name
                    phone
                    status
                    email
                    departmentType
                    role {
                        name
                        current {
                            lang
                            name
                        }
                    }
                    lang
                    locale {
                        name
                        locale
                    }
                    createdAt
                    service {
                        id
                    }
                }
            }',
            $data['id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['roleId'],
            $data['departmentType'],
            $data['serviceId'],
        );
    }

    private function getQueryStrOnlyDealership(array $data): string
    {
        return sprintf('
            mutation {
                adminEdit(input:{
                    id: "%s",
                    dealershipId: "%s",,
                }) {
                    id
                    name
                    dealership {
                        id
                        current {
                            name
                        }
                    }
                }
            }',
            $data['id'],
            $data['dealershipId'],
        );
    }

    private function getQueryStrWithoutField(array $data): string
    {
        return sprintf('
            mutation {
                adminEdit(input:{
                    id: "%s"
                }) {
                    id
                    name
                    phone
                    email
                    role {
                        name
                        current {
                            lang
                            name
                        }
                    }
                }
            }',
            $data['id']
        );
    }

    private function getQueryStrFieldOnlyEmail(array $data): string
    {
        return sprintf('
            mutation {
                adminEdit(input:{
                    id: "%s"
                    email: "%s"
                }) {
                    id
                    name
                    phone
                    email
                    role {
                        name
                        current {
                            lang
                            name
                        }
                    }
                }
            }',
            $data['id'],
            $data['email']
        );
    }
}


