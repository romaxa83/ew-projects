<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Events\Admin\GeneratePassword;
use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Permission\Role;
use App\Services\Localizations\LocalizationService;
use App\Types\Permissions;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class CreateTest extends TestCase
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
    public function create_success()
    {
        \Event::fake([GeneratePassword::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ADMIN_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $role = $this->adminBuilder()->createRole();
        $dealership = Dealership::query()->orderBy(\DB::raw('RAND()'))->first();
        $service = Service::find(1);

        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '+30999922222',
            'roleId' => $role->id,
            'dealershipId' => $dealership->id,
            'departmentType' => $this->department_type_body,
            'serviceId' => $service->id,
        ];
        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.adminCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('dealership', $responseData);
        $this->assertArrayHasKey('id', $responseData['dealership']);
        $this->assertArrayHasKey('current', $responseData['dealership']);
        $this->assertArrayHasKey('name', $responseData['dealership']['current']);
        $this->assertArrayHasKey('departmentType', $responseData);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);

        $this->assertEquals($responseData['email'], $data['email']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['status'], $this->admin_status_active);
        $this->assertEquals($responseData['lang'], app(LocalizationService::class)->getDefaultSlugByAdmin());
        $this->assertEquals($responseData['role']['name'], $role->name);
        $this->assertEquals($responseData['dealership']['id'], $dealership->id);
        $this->assertEquals($responseData['departmentType'], $data['departmentType']);
        $this->assertEquals($responseData['service']['id'], $data['serviceId']);

        $this->assertNotEquals($responseData['phone'], $data['phone']);

        $phone = new Phone($data['phone']);
        $this->assertTrue($phone->compare(new Phone($responseData['phone'])));

        $model = Admin::find($responseData['id']);
        $this->assertNotEmpty($model->password);
        $this->assertEquals($model->dealership->id, $dealership->id);

        \Event::assertDispatched(GeneratePassword::class);
    }

    /** @test */
    public function create_not_unique_email()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $email = 'admin.aa@gmail.com';
        $admin2 = $builder->setEmail($email)->create();

        $role = $this->adminBuilder()->createRole();
        $dealership = Dealership::query()->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'name' => 'test',
            'email' => $email,
            'phone' => '+30999922222',
            'roleId' => $role->id,
            'dealershipId' => $dealership->id,
            'departmentType' => $this->department_type_body,
            'serviceId' => 1
        ];
        $response = $this->graphQL($this->getQuery($data));

        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('validation', $response->json('errors.0.extensions'));
        $this->assertArrayHasKey('input.email', $response->json('errors.0.extensions.validation'));
    }

    /** @test */
    public function create_success_without_role()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ADMIN_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
        ];
        $response = $this->graphQL($this->getQueryWithoutRoleAndPhone($data))
            ->assertOk();

        $responseData = $response->json('data.adminCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('dealership', $responseData);
        $this->assertNull($responseData['dealership']);
    }

    /** @test */
    public function create_not_auth()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '+30999922222',
        ];

        $response = $this->graphQL($this->getQueryWithoutRoleAndPhone($data));

        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function create_not_perm()
    {
        $role = Role::query()->where('name','admin')->first();

        $admin = $this->adminBuilder()->attachRole($role)->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '+30999922222',
        ];

        $response = $this->graphQL($this->getQueryWithoutRoleAndPhone($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQuery(array $data): string
    {
        return sprintf('
            mutation {
                adminCreate(input:{
                    name:"%s",
                    email:"%s"
                    phone:"%s"
                    roleId:"%s"
                    dealershipId:"%s"
                    departmentType: %s
                    serviceId: %s
                }) {
                    id
                    name
                    email
                    phone
                    status
                    lang
                    role {
                        name
                    }
                    dealership {
                        id
                        current {
                            name
                        }
                    }
                    departmentType
                    service {
                        id
                    }
                }
            }',
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['roleId'],
            $data['dealershipId'],
            $data['departmentType'],
            $data['serviceId'],
        );
    }

    private function getQueryWithoutRoleAndPhone(array $data): string
    {
        return sprintf('
            mutation {
                adminCreate(input:{
                    name:"%s",
                    email:"%s"
                }) {
                    id
                    name
                    email
                    phone
                    status
                    lang
                    dealership {
                        id
                    }
                }
            }',
            $data['name'],
            $data['email'],
        );
    }

}

