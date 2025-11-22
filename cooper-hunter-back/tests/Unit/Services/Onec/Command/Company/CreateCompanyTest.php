<?php

namespace Tests\Unit\Services\Onec\Command\Company;

use App\Enums\Requests\RequestCommand;
use App\Models\Companies\Company;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Company\CreateCompany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class CreateCompanyTest extends TestCase
{
    use DatabaseTransactions;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        $model = $this->companyBuilder->withContacts()->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCompany($sender))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::CREATE_COMPANY);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);

        $model->refresh();
        $this->assertEquals($model->guid, data_get($response, 'guid'));
    }

    /** @test */
    public function has_error()
    {
        $model = $this->companyBuilder->withContacts()->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCompany($sender))->handler($model);

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
        /** @var $model Company */
        $model = $this->companyBuilder->withContacts()->create();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($model)->create();
        $address_2 = $this->companyShippingAddressBuilder->setCompany($model)->create();


        $media_1 = UploadedFile::fake()->image('product1.jpg');
        $media_2 = UploadedFile::fake()->image('product2.pdf');

        $model->addMedia($media_1)->toMediaCollection(Company::MEDIA_COLLECTION_NAME);
        $model->addMedia($media_2)->toMediaCollection(Company::MEDIA_COLLECTION_NAME);

        $sender = $this->createStub(RequestClient::class);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        $command = (new CreateCompany($sender));
        $data = $command->transformData($model);

        $format = 'Y-m-d H:i:s';
        $this->assertEquals(data_get($data, 'id'), $model->id);
        $this->assertEquals(data_get($data, 'guid'), $model->guid);
        $this->assertEquals(data_get($data, 'authorization_code'), $model->code);
        $this->assertEquals(data_get($data, 'type'), $model->type);
        $this->assertEquals(data_get($data, 'status'), $model->status);
        $this->assertEquals(data_get($data, 'terms'), $model->terms);
        $this->assertEquals(data_get($data, 'business_name'), $model->business_name);
        $this->assertEquals(data_get($data, 'email'), $model->email);
        $this->assertEquals(data_get($data, 'phone'), $model->phone);
        $this->assertEquals(data_get($data, 'country'), $model->country->country_code);
        $this->assertEquals(data_get($data, 'state'), $model->state->short_name);
        $this->assertEquals(data_get($data, 'city'), $model->city);
        $this->assertEquals(data_get($data, 'address_line_1'), $model->address_line_1);
        $this->assertEquals(data_get($data, 'address_line_2'), $model->address_line_2);
        $this->assertEquals(data_get($data, 'po_box'), $model->po_box);
        $this->assertEquals(data_get($data, 'zip'), $model->zip);
        $this->assertEquals(data_get($data, 'taxpayer_id'), $model->taxpayer_id);
        $this->assertEquals(data_get($data, 'tax'), $model->tax);
        $this->assertEquals(data_get($data, 'websites'), $model->websites);
        $this->assertEquals(data_get($data, 'marketplaces'), $model->marketplaces);
        $this->assertEquals(data_get($data, 'trade_names'), $model->trade_names);
        $this->assertEquals(data_get($data, 'created_at'), $model->created_at?->format($format));

        $this->assertEquals(data_get($data, 'contact_account.name'), $model->contactAccount->name);
        $this->assertEquals(data_get($data, 'contact_account.phone'), $model->contactAccount->phone);
        $this->assertEquals(data_get($data, 'contact_account.email'), $model->contactAccount->email);
        $this->assertEquals(data_get($data, 'contact_account.country'), $model->contactAccount->country->country_code);
        $this->assertEquals(data_get($data, 'contact_account.state'), $model->contactAccount->state->short_name);
        $this->assertEquals(data_get($data, 'contact_account.city'), $model->contactAccount->city);
        $this->assertEquals(data_get($data, 'contact_account.address_line_1'), $model->contactAccount->address_line_1);
        $this->assertEquals(data_get($data, 'contact_account.address_line_2'), $model->contactAccount->address_line_2);
        $this->assertEquals(data_get($data, 'contact_account.po_box'), $model->contactAccount->po_box);
        $this->assertEquals(data_get($data, 'contact_account.zip'), $model->contactAccount->zip);

        $this->assertEquals(data_get($data, 'contact_order.name'), $model->contactOrder->name);
        $this->assertEquals(data_get($data, 'contact_order.phone'), $model->contactOrder->phone);
        $this->assertEquals(data_get($data, 'contact_order.email'), $model->contactOrder->email);
        $this->assertEquals(data_get($data, 'contact_order.country'), $model->contactOrder->country->country_code);
        $this->assertEquals(data_get($data, 'contact_order.state'), $model->contactOrder->state->short_name);
        $this->assertEquals(data_get($data, 'contact_order.city'), $model->contactOrder->city);
        $this->assertEquals(data_get($data, 'contact_order.address_line_1'), $model->contactOrder->address_line_1);
        $this->assertEquals(data_get($data, 'contact_order.address_line_2'), $model->contactOrder->address_line_2);
        $this->assertEquals(data_get($data, 'contact_order.po_box'), $model->contactOrder->po_box);
        $this->assertEquals(data_get($data, 'contact_order.zip'), $model->contactOrder->zip);

        $this->assertEquals(data_get($data, 'locations.0.name'), $address_1->name);
        $this->assertEquals(data_get($data, 'locations.0.phone'), $address_1->phone);
        $this->assertEquals(data_get($data, 'locations.0.fax'), $address_1->fax);
        $this->assertEquals(data_get($data, 'locations.0.email'), $address_1->email);
        $this->assertEquals(data_get($data, 'locations.0.receiving_persona'), $address_1->receiving_persona);
        $this->assertEquals(data_get($data, 'locations.0.country'), $address_1->country->country_code);
        $this->assertEquals(data_get($data, 'locations.0.state'), $address_1->state->short_name);
        $this->assertEquals(data_get($data, 'locations.0.city'), $address_1->city);
        $this->assertEquals(data_get($data, 'locations.0.address_line_1'), $address_1->address_line_1);
        $this->assertEquals(data_get($data, 'locations.0.address_line_2'), $address_1->address_line_2);
        $this->assertEquals(data_get($data, 'locations.0.zip'), $address_1->zip);

        $this->assertEquals(data_get($data, 'locations.1.name'), $address_2->name);
        $this->assertEquals(data_get($data, 'locations.1.phone'), $address_2->phone);
        $this->assertEquals(data_get($data, 'locations.1.fax'), $address_2->fax);
        $this->assertEquals(data_get($data, 'locations.1.email'), $address_2->email);
        $this->assertEquals(data_get($data, 'locations.1.receiving_persona'), $address_2->receiving_persona);
        $this->assertEquals(data_get($data, 'locations.1.country'), $address_2->country->country_code);
        $this->assertEquals(data_get($data, 'locations.1.state'), $address_2->state->short_name);
        $this->assertEquals(data_get($data, 'locations.1.city'), $address_2->city);
        $this->assertEquals(data_get($data, 'locations.1.address_line_1'), $address_2->address_line_1);
        $this->assertEquals(data_get($data, 'locations.1.address_line_2'), $address_2->address_line_2);
        $this->assertEquals(data_get($data, 'locations.1.zip'), $address_2->zip);

        $this->assertEquals(data_get($data, 'media.0'), $model->media[0]->getFullUrl());
        $this->assertEquals(data_get($data, 'media.1'), $model->media[1]->getFullUrl());
    }

    /** @test */
    public function something_wrong()
    {
        $model = $this->companyBuilder->withContacts()->create();

        // эмулируем запрос к 1c
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCompany($sender))->handler($model);

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
}

