<?php

namespace Tests\Feature\Mutations\Order;

use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Models\Catalogs\Service\Service;
use App\Services\Firebase\FcmAction;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class OrderChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use OrderBuilder;
    use CarBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_toggle_from_draft_to_created()
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
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_created,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();
        $this->assertEquals($order->status, Status::CREATED);
        $this->assertNull($order->closed_at);
        $this->assertNull($order->deleted_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));
    }

    /** @test */
    public function success_toggle_from_draft_to_process()
    {
        \Event::fake([
            FcmPush::class
        ]);

        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::CREDIT_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_in_process,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();
        $this->assertEquals($order->status, Status::IN_PROCESS);
        $this->assertNull($order->closed_at);
        $this->assertNull($order->deleted_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::ORDER_ACCEPT;
        });
        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getBody() == __('notification.firebase.action_order_accept.body');
        });
    }

    /** @test */
    public function success_toggle_from_draft_to_done_for_order_relation_system()
    {
        \Event::fake([
            FcmPush::class
        ]);

        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::CREDIT_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->withAdditions()
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_done,
        ];

        $this->assertTrue($order->isRelateToSystem());
        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();
        $this->assertEquals($order->status, Status::CLOSE);
        $this->assertNotNull($order->closed_at);
        $this->assertNull($order->deleted_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::ORDER_COMPLETE;
        });
        \Event::assertDispatched(function (FcmPush $event) use ($order) {

            return $event->action->getBody() == __('notification.firebase.action_order_complete.body', [
                    'service' => $order->service->current->name,
                    'number' => null,
                    'car' => ' '
                ]);
        });
    }

    /** @test */
    public function success_toggle_from_draft_to_done_for_order_relation_aa()
    {
        \Event::fake([
            FcmPush::class
        ]);

        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::BODY_ALIAS)->first();
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setCarId($car->id)
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_done,
        ];

        $order->refresh();

        $this->assertNotEquals($order->status, $data['status']);
        $this->assertTrue($order->isRelateToAA());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();

        $this->assertEquals($order->status, Status::DONE);
        $this->assertNull($order->closed_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::ORDER_COMPLETE;
        });
        \Event::assertDispatched(function (FcmPush $event) use ($order) {
            return $event->action->getBody() == __('notification.firebase.action_order_complete.body', [
                    'service' => $order->service->current->name,
                    'number' => $order->additions->car->number->getValue(),
                    'car' => $order->additions->car->car_name
                ]);
        });
    }

    /** @test */
    public function success_toggle_from_draft_to_close()
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
            ->setStatus(Status::DRAFT)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_close,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();
        $this->assertEquals($order->status, Status::CLOSE);
        $this->assertNotNull($order->closed_at);
        $this->assertNull($order->deleted_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));
    }

    /** @test */
    public function success_toggle_from_draft_to_reject()
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
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_reject,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();
        $this->assertEquals($order->status, Status::REJECT);
        $this->assertNull($order->closed_at);
        $this->assertNotNull($order->deleted_at);

        $this->assertArrayHasKey('id', data_get($response, 'data.orderChangeStatus'));
        $this->assertArrayHasKey('status', data_get($response, 'data.orderChangeStatus'));

        $this->assertEquals($order->id, data_get($response, 'data.orderChangeStatus.id'));
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
            'status' => $this->order_status_reject,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.not change status order close'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    // @todo
    public function toggle_if_order_not_related_system()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'status' => $this->order_status_created,
        ];

        $this->assertNotEquals($order->status, $data['status']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.order not support action'), $response->json('errors.0.message'));
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
            'status' =>'wrong',
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
            'status' => $this->order_status_created,
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
            'status' => $this->order_status_created,
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
            'status' => $this->order_status_created,
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
                orderChangeStatus(input: {
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

