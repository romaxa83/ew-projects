<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Order\Parts;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\DeliveryBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected OrderBuilder $orderBuilder;
    protected DeliveryBuilder $deliveryBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->deliveryBuilder = resolve(DeliveryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $command OrderChangeStatusCommand */
        $command = resolve(OrderChangeStatusCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['status'], $model->status->toUpperCase());
        $this->assertFalse(isset($res['deliveries']));
    }

    /** @test */
    public function check_prepare_data_with_deliveries()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Sent())->create();
        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        /** @var $command OrderChangeStatusCommand */
        $command = resolve(OrderChangeStatusCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['status'], $model->status->toUpperCase());
        $this->assertEquals($res['deliveries'][0]['guid'], $delivery->id);
        $this->assertEquals($res['deliveries'][0]['tracking_number'], $delivery->tracking_number);
        $this->assertEquals($res['deliveries'][0]['method'], $delivery->method->toUpperCase());
        $this->assertEquals($res['deliveries'][0]['status'], $delivery->status->toUpperCase());
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $command OrderChangeStatusCommand */
        $command = resolve(OrderChangeStatusCommand::class);

        $this->assertEquals(
            $command->getUri($command->beforeRequestForData($model)),
            str_replace('{id}', $model->id, config("requests.e_com.paths.order.parts.change_status"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command OrderChangeStatusCommand */
        $command = resolve(OrderChangeStatusCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [OrderChangeStatusCommand] you need to pass 'id' to uri"
        );

        $command->getUri($data);
    }
}

