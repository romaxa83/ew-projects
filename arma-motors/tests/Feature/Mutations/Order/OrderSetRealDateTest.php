<?php

namespace Tests\Feature\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\Helpers\DateTime;
use App\Models\Catalogs\Service\Service;
use App\Types\Order\Status;
use App\Types\Permissions;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class OrderSetRealDateTest extends TestCase
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
            ->setStatus(Status::CREATED)
            ->setDealershipId(1)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds(Carbon::now()->timestamp),
        ];

        $this->assertNotEquals($order->additions->real_date, $data['realDate']);
        $this->assertNull($order->additions->for_current_filter_date);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();

        $this->assertArrayHasKey('id', data_get($response, 'data.orderSetRealDate'));
        $this->assertArrayHasKey('realDate', data_get($response, 'data.orderSetRealDate.additions'));

        $this->assertEquals($order->id, data_get($response, 'data.orderSetRealDate.id'));
        $this->assertEquals($order->additions->real_date->timestamp, DateTime::fromMillisecondToSeconds($data['realDate']));
        $this->assertEquals($order->additions->for_current_filter_date->timestamp, DateTime::fromMillisecondToSeconds($data['realDate']));
    }

    /** @test */
    public function time_is_Busy()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::BODY_ALIAS)->first();

        $timestamp = CarbonImmutable::now()->timestamp;

        $this->orderBuilder()
            ->setServiceId($service->id)
            ->setStatus(Status::CREATED)
            ->setDealershipId(1)
            ->setRealDate($timestamp)
            ->withAdditions()
            ->asOne()
            ->create();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::CREATED)
            ->setDealershipId(1)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds($timestamp),
        ];

        $this->assertNotEquals($order->additions->real_date, $data['realDate']);
        $this->assertNull($order->additions->for_current_filter_date);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__("error.order.real time is busy"), $response->json('errors.0.message'));
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

        $service = Service::whereAlias(Service::BODY_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->setDealershipId(1)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds(Carbon::now()->timestamp),
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__("error.order.order must be create and process status"), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function without_dealership()
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
            ->setStatus(Status::CREATED)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds(Carbon::now()->timestamp),
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__("error.order.order must have dealership"), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_support_service()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::whereAlias(Service::INSURANCE_CASCO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->setDealershipId(1)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds(Carbon::now()->timestamp),
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__("error.order.order not support action"), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function if_exist_order()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $date = Carbon::now()->timestamp;

        $service = Service::whereAlias(Service::BODY_ALIAS)->first();
        $order = $this->orderBuilder()
            ->setServiceId($service->id)
            ->setStatus(Status::CREATED)
            ->setDealershipId(1)
            ->withAdditions()
            ->asOne()
            ->create();

        $this->orderBuilder()
            ->setServiceId($service->id)
            ->setStatus(Status::DRAFT)
            ->withAdditions()
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)DateTime::fromSecondsToMilliseconds($date),
        ];

        $this->assertNotEquals($order->additions->real_date, $data['realDate']);
        $this->assertNull($order->additions->for_current_filter_date);


        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $order->refresh();

        $this->assertArrayHasKey('id', data_get($response, 'data.orderSetRealDate'));
        $this->assertArrayHasKey('realDate', data_get($response, 'data.orderSetRealDate.additions'));

        $this->assertEquals($order->id, data_get($response, 'data.orderSetRealDate.id'));
        $this->assertEquals($order->additions->real_date->timestamp, DateTime::fromMillisecondToSeconds($data['realDate']));
        $this->assertEquals($order->additions->for_current_filter_date->timestamp, DateTime::fromMillisecondToSeconds($data['realDate']));
    }

    /** @test */
    public function not_found()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 999,
            'realDate' => (string)Carbon::now()->timestamp,
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

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)Carbon::now()->timestamp,
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

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->asOne()
            ->create();

        $data = [
            'id' => $order->id,
            'realDate' => (string)Carbon::now()->timestamp,
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
                orderSetRealDate(input: {
                    id: "%s"
                    realDate: "%s"
                }) {
                    id
                    additions{
                        realDate
                    }
                }
            }',
            $data['id'],
            $data['realDate'],
        );
    }
}

