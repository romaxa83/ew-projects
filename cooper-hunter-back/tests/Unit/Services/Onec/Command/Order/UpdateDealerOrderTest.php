<?php

namespace Tests\Unit\Services\Onec\Command\Order;

use App\Enums\Requests\RequestCommand;
use App\Models\Orders\Dealer\Order;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Order\CreateDealerOrder;
use App\Services\OneC\Commands\Order\UpdateDealerOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class UpdateDealerOrderTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        /** @var $order Order */
        $model = $this->orderBuilder->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNotNull($model->guid);
        $this->assertNull(Request::first());

        (new UpdateDealerOrder($sender))->handler($model);

        $record = Request::first();
        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::UPDATE_DEALER_ORDER);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);
    }

    /** @test */
    public function has_error()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'guid' => null
        ])->create();

        // эмулируем запрос к 1c
        $response = self::errorResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull($model->error);
        $this->assertNull(Request::first());

        (new CreateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertNotNull($record->send_data);

        $model->refresh();

        $this->assertNull($model->guid);
        $this->assertEquals($model->error, $response['error'][0]);
    }

    /** @test */
    public function has_sys_error()
    {
        /** @var $order Order */
        $model = $this->orderBuilder->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull(Request::first());

        (new UpdateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->response_data, $response['error']);
        $this->assertNotNull($record->send_data);

        $model->refresh();
    }

    /** @test */
    public function check_transform_data()
    {
        /** @var $order Order */
        $model = $this->orderBuilder->create();

        $sender = $this->createStub(RequestClient::class);

        $this->assertNull(Request::first());

        $command = (new UpdateDealerOrder($sender));
        $data = $command->transformData($model);

        $format = 'Y-m-d H:i:s';
        $this->assertEquals(data_get($data, 'id'), $model->id);
        $this->assertEquals(data_get($data, 'guid'), $model->guid);
        $this->assertEquals(data_get($data, 'created_at'), $model->created_at?->format($format));
    }

    /** @test */
    public function something_wrong()
    {
        /** @var $order Order */
        $model = $this->orderBuilder->create();

        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        $this->assertNull(Request::first());

        (new UpdateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->response_data, 'some_message');

        $model->refresh();
    }

    public static function successResponseData(): array
    {
        return [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];
    }

    public static function errorResponseData(): array
    {
        return [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ["some error"]
        ];
    }
}
