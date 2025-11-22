<?php

namespace Tests\Feature\Queries\Admin;

use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\Department;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class AdminListForOrderTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $dealership = Dealership::find(1);
        $service = Service::query()->where('alias', Service::BODY_ALIAS)->first();

        $this->adminBuilder()->setEmail('admin1@gmail.com')
            ->setDepartmentType(Department::TYPE_BODY)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin2@gmail.com')
            ->setDepartmentType(Department::TYPE_BODY)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin3@gmail.com')
            ->setDepartmentType(Department::TYPE_SALES)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin4@gmail.com')
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin5@gmail.com')
            ->create();

        $order = $this->orderBuilder()
            ->setServiceId($service->id)
            ->setDealershipId($dealership->id)
            ->asOne()->create();

        $response = $this->graphQL($this->getQueryStr($order->id))
            ->assertOk();

        $responseData = $response->json('data.adminListForOrder');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('departmentType', $responseData[0]);
        $this->assertArrayHasKey('dealership', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['dealership']);

        $this->assertEquals(2, count($responseData));

        foreach ($responseData as $data){
            $this->assertEquals($this->department_type_body, $data['departmentType']);
            $this->assertEquals($dealership->id, $data['dealership']['id']);
        }
    }

    /** @test */
    public function success_another_service()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $dealership = Dealership::find(1);
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $this->adminBuilder()->setEmail('admin1@gmail.com')
            ->setDepartmentType(Department::TYPE_BODY)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin2@gmail.com')
            ->setDepartmentType(Department::TYPE_BODY)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin3@gmail.com')
            ->setDepartmentType(Department::TYPE_SERVICE)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin4@gmail.com')
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin5@gmail.com')
            ->create();

        $order = $this->orderBuilder()
            ->setServiceId($service->id)
            ->setDealershipId($dealership->id)
            ->asOne()->create();

        $response = $this->graphQL($this->getQueryStr($order->id))
            ->assertOk();
        $responseData = $response->json('data.adminListForOrder');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('departmentType', $responseData[0]);
        $this->assertArrayHasKey('dealership', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['dealership']);

        $this->assertEquals(1, count($responseData));

        foreach ($responseData as $data){
            $this->assertEquals($this->department_type_service, $data['departmentType']);
            $this->assertEquals($dealership->id, $data['dealership']['id']);
        }
    }

    /** @test */
    public function order_without_dealership()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $dealership = Dealership::find(1);
        $service = Service::query()->where('alias', Service::CREDIT_ALIAS)->first();

        $this->adminBuilder()->setEmail('admin1@gmail.com')
            ->setDepartmentType(Department::TYPE_BODY)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin2@gmail.com')
            ->setDepartmentType(Department::TYPE_CREDIT)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin3@gmail.com')
            ->setDepartmentType(Department::TYPE_CREDIT)
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin4@gmail.com')
            ->withDealership($dealership)->create();
        $this->adminBuilder()->setEmail('admin5@gmail.com')
            ->setDepartmentType(Department::TYPE_CREDIT)
            ->create();

        $order = $this->orderBuilder()
            ->setServiceId($service->id)
            ->asOne()->create();

        $response = $this->graphQL($this->getQueryStr($order->id))
            ->assertOk();

        $responseData = $response->json('data.adminListForOrder');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('departmentType', $responseData[0]);
        $this->assertArrayHasKey('dealership', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['dealership']);

        $this->assertEquals(3, count($responseData));

        foreach ($responseData as $data){
            $this->assertEquals($this->department_type_credit, $data['departmentType']);
        }
    }

    public function getQueryStr($orderId): string
    {
        return  sprintf('{
            adminListForOrder(orderId: %d) {
                id
                departmentType
                dealership {
                    id
                }
               }
            }',
            $orderId
        );
    }
}

