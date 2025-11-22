<?php

namespace Tests\Feature\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class OrderChangePaymentStatusTest extends TestCase
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

        $service = Service::whereAlias(Service::BODY_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_payment_status_part,
        ];

        $this->assertNotEquals($order->payment_status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();

        $this->assertEquals($order->payment_status, PaymentStatus::PART);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangePaymentStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangePaymentStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangePaymentStatus.id'));
    }


    /** @test */
    public function toggle_if_order_close()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::CREDIT_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::CLOSE)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_payment_status_part,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.not change status order close'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function wrong_status()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $data = [
            'id' => $order->id,
            'status' => 'wrong',
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_found()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);;

        $data = [
            'id' => 999,
            'status' => $this->order_payment_status_part,
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

        $data = [
            'id' => 1,
            'status' => $this->order_payment_status_part,
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

        $data = [
            'id' => 1,
            'status' => $this->order_payment_status_part,
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
                orderChangePaymentStatus(input: {
                    id: "%s"
                    status: %s
                }) {
                    id
                    status
                }
            }',
            $data['id'],
            $data['status'],
        );
    }
}

