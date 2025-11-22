<?php

namespace Tests\Feature\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class OrderAttachAdminTest extends TestCase
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
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::query()->where('alias', Service::CREDIT_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)->asOne()->create();

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();

        $data = [
            'id' => $order->id,
            'adminId' => $someAdmin->id,
        ];

        $this->assertNotEquals($order->admin_id, $data['adminId']);
        $this->assertEquals($order->status, Status::DRAFT);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();

        $this->assertEquals($order->admin_id, $data['adminId']);
        $this->assertEquals($order->status, Status::CREATED);
        $this->assertTrue($order->state->isCreated());

        $responseData = $response->json('data.orderAttachAdmin');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('admin', $responseData);
        $this->assertArrayHasKey('id', $responseData['admin']);

        $this->assertEquals($responseData['status'], $this->order_status_created);
        $this->assertEquals($order->admin->id, $responseData['admin']['id']);
    }


    public function fail_order_not_related_system()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)->asOne()->create();

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();

        $data = [
            'id' => $order->id,
            'adminId' => $someAdmin->id,
        ];

        $this->assertNotEquals($order->admin_id, $data['adminId']);
        $this->assertEquals($order->status, Status::DRAFT);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.order not support action'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function remove_admin()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::query()->where('alias', Service::CREDIT_ALIAS)->first();

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)->setAdminId($someAdmin->id)->asOne()->create();

        $data = [
            'id' => $order->id,
            'adminId' => "",
        ];

        $this->assertEquals($order->admin_id, $someAdmin->id);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $this->assertNull($response->json('data.orderAttachAdmin.admin'));


        $order->refresh();
        $this->assertNull($order->admin_id);
    }

    /** @test */
    public function not_found()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();

        $data = [
            'id' => 1,
            'adminId' => $someAdmin->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();

        $data = [
            'id' => 1,
            'adminId' => $someAdmin->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $adminBuilder->setEmail('some@gmail.com')->create();

        $data = [
            'id' => 1,
            'adminId' => $someAdmin->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                orderAttachAdmin(input: {
                    id: "%s"
                    adminId: "%s"
                }) {
                    id
                    status
                    admin {
                        id
                    }
                }
            }',
            $data['id'],
            $data['adminId'],
        );
    }
}

