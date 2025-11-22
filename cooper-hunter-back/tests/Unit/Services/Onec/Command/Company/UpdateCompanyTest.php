<?php

namespace Tests\Unit\Services\Onec\Command\Company;

use App\Enums\Requests\RequestCommand;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Company\UpdateCompany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class UpdateCompanyTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        $model = $this->companyBuilder->withContacts()->setData([
            'guid' => $this->faker->uuid
        ])->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNotNull($model->guid);
        $this->assertNull(Request::first());

        (new UpdateCompany($sender))->handler($model);

        $record = Request::first();
        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::UPDATE_COMPANY);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);
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

        (new UpdateCompany($sender))->handler($model);

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
        $model = $this->companyBuilder->withContacts()->setData([
            'terms' => [
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ],
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ]
            ]
        ])->create();

        $sender = $this->createStub(RequestClient::class);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        $command = (new UpdateCompany($sender));
        $data = $command->transformData($model);

        $format = 'Y-m-d H:i:s';
        $this->assertEquals(data_get($data, 'id'), $model->id);
        $this->assertEquals(data_get($data, 'guid'), $model->guid);
        $this->assertEquals(data_get($data, 'created_at'), $model->created_at?->format($format));
        $this->assertEquals(data_get($data, 'terms'), $model->terms);
    }

    /** @test */
    public function something_wrong()
    {
        $model = $this->companyBuilder->withContacts()->create();

        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new UpdateCompany($sender))->handler($model);

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
