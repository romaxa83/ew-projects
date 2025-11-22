<?php

namespace Tests\Unit\Services\Onec\Command\CommercialProject;

use App\Enums\Warranties\WarrantyType;
use App\Models\Commercial\CommercialProject;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\CommercialProject\CreateCommercialProject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class CreateCommercialProjectTest extends TestCase
{
    use DatabaseTransactions;

    protected $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        $model = $this->projectBuilder->create();

        // эмулируем запрос к AA
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCommercialProject(
            $sender,
        ))->handler($model);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);

        $model->refresh();
        $this->assertEquals($model->guid, data_get($response, 'guid'));
    }

    /** @test */
    public function has_error()
    {
        $model = $this->projectBuilder->create();

        // эмулируем запрос к AA
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCommercialProject(
            $sender,
        ))->handler($model);

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
        /** @var $model CommercialProject */
        $model = $this->projectBuilder->create();

        $sender = $this->createStub(RequestClient::class);

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        $command = (new CreateCommercialProject($sender,));
        $data = $command->transformData($model);

        $format = 'Y-m-d H:i:s';
        $this->assertEquals(data_get($data, 'id'), $model->id);
        $this->assertEquals(data_get($data, 'guid'), $model->guid);
        $this->assertEquals(data_get($data, 'name'), $model->name);
        $this->assertEquals(data_get($data, 'status'), $model->status);
        $this->assertEquals(data_get($data, 'type'), WarrantyType::COMMERCIAL);
        $this->assertEquals(data_get($data, 'address_line_1'), $model->address_line_1);
        $this->assertEquals(data_get($data, 'address_line_2'), $model->address_line_2);
        $this->assertEquals(data_get($data, 'city'), $model->city);
        $this->assertEquals(data_get($data, 'country'), $model->country->country_code);
        $this->assertEquals(data_get($data, 'state'), $model->state->short_name);
        $this->assertEquals(data_get($data, 'zip'), $model->zip);
        $this->assertEquals(data_get($data, 'first_name'), $model->first_name);
        $this->assertEquals(data_get($data, 'last_name'), $model->last_name);
        $this->assertEquals(data_get($data, 'phone'), $model->phone);
        $this->assertEquals(data_get($data, 'email'), $model->email);
        $this->assertEquals(data_get($data, 'company_name'), $model->company_name);
        $this->assertEquals(data_get($data, 'company_address'), $model->company_address);
        $this->assertEquals(data_get($data, 'description'), $model->description);
        $this->assertEquals(data_get($data, 'estimate_start_date'), $model->estimate_start_date?->format($format));
        $this->assertEquals(data_get($data, 'estimate_end_date'), $model->estimate_end_date?->format($format));
        $this->assertEquals(data_get($data, 'created_at'), $model->created_at?->format($format));
        $this->assertEquals(data_get($data, 'request_until'), $model->request_until?->format($format));
        $this->assertEquals(data_get($data, 'technician.guid'), $model->member->guid);
        $this->assertEquals(data_get($data, 'technician.name'), $model->member->full_name);
        $this->assertEquals(data_get($data, 'technician.email'), $model->member->email->getValue());
    }

    /** @test */
    public function something_wrong()
    {
        $model = $this->projectBuilder->create();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        $this->assertNull($model->guid);
        $this->assertNull(Request::first());

        (new CreateCommercialProject(
            $sender,
        ))->handler($model);

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


