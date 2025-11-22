<?php

namespace Tests\Unit\Services\Onec\Command\Order;

use App\Enums\Requests\RequestCommand;
use App\Models\Orders\Dealer\Order;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Order\CreateDealerOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class CreateDealerOrderTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DealerBuilder $dealerBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected ProductBuilder $productBuilder;
    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'guid' => null
        ])->create();
        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::CREATE_DEALER_ORDER);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);

        $model->refresh();
        $this->assertEquals($model->guid, data_get($response, 'guid'));
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
        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'guid' => null
        ])->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->response_data, $response['error']);
        $this->assertNotNull($record->send_data);

        $model->refresh();
        $this->assertNull($model->guid);
    }

    /** @test */
    public function check_transform_data()
    {
        $company = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $address = $this->addressBuilder->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setShippingAddress($address)->setData([
            'guid' => null
        ])->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setOrder($model)->setProduct($product_1)->create();
        $item_2 = $this->itemBuilder->setOrder($model)->setProduct($product_2)->create();

        $media_1 = UploadedFile::fake()->image('product1.jpg');
        $media_2 = UploadedFile::fake()->image('product2.pdf');

        $model->addMedia($media_1)->toMediaCollection(Order::MEDIA_COLLECTION_NAME);
        $model->addMedia($media_2)->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $sender = $this->createStub(RequestClient::class);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        $command = (new CreateDealerOrder($sender));
        $data = $command->transformData($model);

        $format = 'Y-m-d H:i:s';
        $this->assertEquals(data_get($data, 'id'), $model->id);
        $this->assertEquals(data_get($data, 'guid'), $model->guid);
        $this->assertEquals(data_get($data, 'po'), $model->po);
        $this->assertEquals(data_get($data, 'delivery_type'), $model->delivery_type->value);
        $this->assertEquals(data_get($data, 'payment_type'), $model->payment_type->value);
        $this->assertEquals(data_get($data, 'type'), $model->type->value);
        $this->assertEquals(data_get($data, 'comment'), $model->comment);
        $this->assertEquals(data_get($data, 'created_at'), $model->created_at?->format($format));
        $this->assertEquals(data_get($data, 'payment_card_guid'), $model->paymentCard?->guid);
        $this->assertEquals(data_get($data, 'company.guid'), $company->guid);
        $this->assertEquals(data_get($data, 'error'), $model->error);

        $this->assertEquals(data_get($data, 'media.0'), $model->media[0]->getFullUrl());
        $this->assertEquals(data_get($data, 'media.1'), $model->media[1]->getFullUrl());

        $this->assertEquals(data_get($data, 'location.name'), $address->name);
        $this->assertEquals(data_get($data, 'location.phone'), $address->phone->getValue());
        $this->assertEquals(data_get($data, 'location.fax'), $address->fax->getValue());
        $this->assertEquals(data_get($data, 'location.email'), $address->email->getValue());
        $this->assertEquals(data_get($data, 'location.receiving_persona'), $address->receiving_persona);
        $this->assertEquals(data_get($data, 'location.country'), $address->country->country_code);
        $this->assertEquals(data_get($data, 'location.state'), $address->state->short_name);
        $this->assertEquals(data_get($data, 'location.city'), $address->city);
        $this->assertEquals(data_get($data, 'location.address_line_1'), $address->address_line_1);
        $this->assertEquals(data_get($data, 'location.address_line_2'), $address->address_line_2);
        $this->assertEquals(data_get($data, 'location.po_box'), $address->po_box);
        $this->assertEquals(data_get($data, 'location.zip'), $address->zip);

        $this->assertEquals(data_get($data, 'products.0.guid'), $product_1->guid);
        $this->assertEquals(data_get($data, 'products.0.price'), $item_1->price);
        $this->assertEquals(data_get($data, 'products.0.qty'), $item_1->qty);

        $this->assertEquals(data_get($data, 'products.1.guid'), $product_2->guid);
        $this->assertEquals(data_get($data, 'products.1.price'), $item_2->price);
        $this->assertEquals(data_get($data, 'products.1.qty'), $item_2->qty);
    }

    /** @test */
    public function something_wrong()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'guid' => null
        ])->create();

        // эмулируем запрос к 1c
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateDealerOrder($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->response_data, 'some_message');

        $model->refresh();
        $this->assertNull($model->guid);
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
            "success" => false,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ["some error"]
        ];
    }
}
