<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Order\Parts;

use App\Enums\Orders\Parts\DeliveryType;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderUpdateCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\DeliveryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected DeliveryBuilder $deliveryBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->deliveryBuilder = resolve(DeliveryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->order($model)->create();
        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        /** @var $command OrderUpdateCommand */
        $command = resolve(OrderUpdateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['is_paid'], $model->is_paid);
        $this->assertEquals($res['has_tax_exemption'], $model->with_tax_exemption);
        $this->assertEquals($res['status'], $model->status->toUpperCase());
        $this->assertEquals($res['payment_type'], $model->payment_method->toUpperCase());
        $this->assertEquals($res['delivery_type'], $model->delivery_type->toUpperCase());

        $this->assertEquals($res['deliveryAddress']['first_name'], $model->delivery_address->first_name);
        $this->assertEquals($res['deliveryAddress']['last_name'], $model->delivery_address->last_name);
        $this->assertEquals($res['deliveryAddress']['company'], $model->delivery_address->company);
        $this->assertEquals($res['deliveryAddress']['address'], $model->delivery_address->address);
        $this->assertEquals($res['deliveryAddress']['city'], $model->delivery_address->city);
        $this->assertEquals($res['deliveryAddress']['state'], $model->delivery_address->state);
        $this->assertEquals($res['deliveryAddress']['zip_code'], $model->delivery_address->zip);
        $this->assertEquals($res['deliveryAddress']['phone'], $model->delivery_address->phone->getValue());

        $this->assertEquals($res['billingAddress']['first_name'], $model->billing_address->first_name);
        $this->assertEquals($res['billingAddress']['last_name'], $model->billing_address->last_name);
        $this->assertEquals($res['billingAddress']['company'], $model->billing_address->company);
        $this->assertEquals($res['billingAddress']['address'], $model->billing_address->address);
        $this->assertEquals($res['billingAddress']['city'], $model->billing_address->city);
        $this->assertEquals($res['billingAddress']['state'], $model->billing_address->state);
        $this->assertEquals($res['billingAddress']['zip_code'], $model->billing_address->zip);
        $this->assertEquals($res['billingAddress']['phone'], $model->billing_address->phone->getValue());

        $this->assertEquals($res['deliveries'][0]['guid'], $delivery->id);
        $this->assertEquals($res['deliveries'][0]['tracking_number'], $delivery->tracking_number);
        $this->assertEquals($res['deliveries'][0]['method'], $delivery->method->toUpperCase());
        $this->assertEquals($res['deliveries'][0]['status'], $delivery->status->toUpperCase());

        $this->assertEquals($res['items'][0]['guid'], $item->inventory_id);
        $this->assertEquals($res['items'][0]['count'], $item->qty);
        $this->assertEquals($res['items'][0]['cost'], $item->getPrice());
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        /** @var $command OrderUpdateCommand */
        $command = resolve(OrderUpdateCommand::class);
        $this->assertEquals(
            $command->getUri($command->beforeRequestForData($model)),
            str_replace('{id}', $model->id, config("requests.e_com.paths.order.parts.update"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command OrderUpdateCommand */
        $command = resolve(OrderUpdateCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [OrderUpdateCommand] you need to pass 'id' to uri"
        );

        $command->getUri($data);
    }
}
