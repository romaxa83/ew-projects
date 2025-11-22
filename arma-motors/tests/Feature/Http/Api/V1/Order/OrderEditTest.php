<?php

namespace Tests\Feature\Http\Api\V1\Order;

use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class OrderEditTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function success()
    {
        \Event::fake([FcmPush::class]);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->withAdditions()
            ->create();

        $order->refresh();

        $this->assertFalse($order->send_push_process);

        $data = [
            'status' => Status::CREATED,
            'statusPayment' => PaymentStatus::PART,
            'responsible' => 'test test',
        ];

        $this->assertNotEquals($order->status, $data['status']);
//        $this->assertNotEquals($order->payment_status, $data['statusPayment']);
        $this->assertNotEquals($order->additions->responsible, $data['responsible']);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertEquals($order->status, $data['status']);
//        $this->assertEquals($order->payment_status, $data['statusPayment']);
        $this->assertEquals($order->additions->responsible, $data['responsible']);
        $this->assertTrue($order->send_push_process);

        \Event::assertDispatched(FcmPush::class);
    }

    /** @test */
    public function success_status_created_not_send_push()
    {
        \Event::fake([FcmPush::class]);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::DRAFT)
            ->asOne()
            ->withAdditions()
            ->create();

        $order->refresh();
        $order->update(['send_push_process' => true]);

        $data = [
            'status' => Status::CREATED,
            'statusPayment' => PaymentStatus::PART,
            'responsible' => 'test test',
        ];

        $this->assertNotEquals($order->status, $data['status']);
//        $this->assertNotEquals($order->payment_status, $data['statusPayment']);
        $this->assertNotEquals($order->additions->responsible, $data['responsible']);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertEquals($order->status, $data['status']);
//        $this->assertEquals($order->payment_status, $data['statusPayment']);
        $this->assertEquals($order->additions->responsible, $data['responsible']);
        $this->assertTrue($order->send_push_process);

        \Event::assertNotDispatched(FcmPush::class);
    }

    /** @test */
    public function success_only_statuses()
    {
        \Event::fake([FcmPush::class]);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::IN_PROCESS,
            'statusPayment' => PaymentStatus::PART,
        ];

        $this->assertNotEquals($order->status, $data['status']);
//        $this->assertNotEquals($order->payment_status, $data['statusPayment']);
        $this->assertNull($order->additions->responsible);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertEquals($order->status, $data['status']);
//        $this->assertEquals($order->payment_status, $data['statusPayment']);
        $this->assertNull($order->additions->responsible);

        \Event::assertNotDispatched(FcmPush::class);
    }

    /** @test */
    public function fail_wrong_status()
    {
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => 22,
            'statusPayment' => PaymentStatus::PART,
        ];

        $this->assertNotEquals($order->status, $data['status']);
        $this->assertNotEquals($order->payment_status, $data['statusPayment']);
        $this->assertNull($order->additions->responsible);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        );

        $this->assertEquals($response->json('data'),
            __('validation.in', ['attribute' => __('validation.attributes.status')])
        );
        $this->assertFalse($response->json('success'));
    }

    public function fail_wrong_payment_status()
    {
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::DONE,
            'statusPayment' => PaymentStatus::NONE,
        ];

        $this->assertNotEquals($order->status, $data['status']);
        $this->assertNull($order->additions->responsible);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        );

        $this->assertEquals($response->json('data'),
            __('validation.in', ['attribute' => 'status payment'])
        );
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function success_close()
    {
        \Event::fake([FcmPush::class]);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $order->refresh();

        $this->assertFalse($order->send_push_close);

        $data = [
            'status' => Status::DONE,
            'statusPayment' => PaymentStatus::FULL,
        ];

        $this->assertNotEquals($order->status, $data['status']);
        $this->assertNotEquals($order->payment_status, $data['statusPayment']);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertEquals($order->status, Status::DONE);
//        $this->assertEquals($order->payment_status, $data['statusPayment']);
        $this->assertTrue($order->send_push_close);

        \Event::assertDispatched(FcmPush::class);
    }

    /** @test */
    public function success_close_not_send_push()
    {
        \Event::fake([FcmPush::class]);

        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $order->refresh();
        $order->update(["send_push_close" => true]);

        $data = [
            'status' => Status::DONE,
            'statusPayment' => PaymentStatus::FULL,
        ];

        $this->assertNotEquals($order->status, $data['status']);
        $this->assertNotEquals($order->payment_status, $data['statusPayment']);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertEquals($order->status, Status::DONE);
//        $this->assertEquals($order->payment_status, $data['statusPayment']);
        $this->assertTrue($order->send_push_close);

        \Event::assertNotDispatched(FcmPush::class);
    }

    /** @test */
    public function edit_real_date()
    {
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->setOnDate(Carbon::now()->subDay())
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::DONE,
            'statusPayment' => PaymentStatus::FULL,
            'realDate' => 1631134800
        ];

        $this->assertNull($order->additions->real_date);
        $this->assertNotNull($order->additions->on_date);
        $this->assertNotNull($order->additions->for_current_filter_date);
        $this->assertEquals($order->additions->for_current_filter_date, $order->additions->on_date);

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $order->refresh();

        $this->assertNotNull($order->additions->real_date);
        $this->assertEquals($order->additions->real_date->timestamp, $data['realDate']);

        $this->assertNotEquals($order->additions->for_current_filter_date, $order->additions->on_date);
        $this->assertEquals($order->additions->for_current_filter_date, $order->additions->real_date);
    }

    /** @test */
    public function fail_without_status()
    {
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::IN_PROCESS,
        ];

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        );

//        $this->assertEquals($response->json('data'), __('validation.filled', ['attribute' => 'status payment']));
        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function fail_not_edit_order()
    {
        $service = Service::query()->where('alias', Service::CREDIT_ALIAS)->first();
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::DONE,
            'statusPayment' => PaymentStatus::FULL,
        ];

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => $order->uuid->getValue()]),
            $data,
            $this->headers()
        )
            ->assertStatus(ErrorsCode::BAD_REQUEST);

        $this->assertEquals($response->json('data'), __(__('error.order.order not support action')));
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function not_found()
    {
        $service = Service::query()->where('alias', Service::SERVICE_TO_ALIAS)->first();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setServiceId($service->id)
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98f0c26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::IN_PROCESS,
            'statusPayment' => PaymentStatus::PART,
        ];

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => 'wrong']),
            $data,
            $this->headers()
        )->assertStatus(ErrorsCode::NOT_FOUND);

        $this->assertEquals($response->json('data'), "Not found order by [orderId - wrong]");
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function wrong_auth_token()
    {
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::IN_PROCESS,
            'statusPayment' => PaymentStatus::PART,
        ];

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => '4e5d19f0-fc22-11eb-8274-4cd98fc26f15']),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(Status::CREATED)
            ->asOne()
            ->withAdditions()
            ->create();

        $data = [
            'status' => Status::IN_PROCESS,
            'statusPayment' => PaymentStatus::PART,
        ];

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.order.edit',['orderId' => '4e5d19f0-fc22-11eb-8274-4cd98fc26f15']),
            $data,
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Missing authorization header');
        $this->assertFalse($response->json('success'));
    }
}



