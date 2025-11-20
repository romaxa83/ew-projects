<?php

namespace Tests\Unit\Service\JD;

use App\Models\JD\Client;
use App\Models\JD\Region;
use App\Services\JD\ClientService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create_from_import(): void
    {
        $service = app(ClientService::class);
        $region = Region::query()->first();
        $data = [
            "id" => 232234,
            "customer_id" => "FD567",
            "company_name" => "test company",
            "customer_first_name" => "test first name",
            "customer_last_name" => "test last name",
            "customer_second_name" => "test second name",
            "phone" => "86657656978678",
            "status" => true,
            "created_at" => Carbon::now(),
            "region_id" => $region->id,
        ];

        $result = $service->createFromImport($data);

        $this->assertEquals($result->jd_id, $data["id"]);
        $this->assertEquals($result->customer_id, $data["customer_id"]);
        $this->assertEquals($result->company_name, $data["company_name"]);
        $this->assertEquals($result->customer_first_name, $data["customer_first_name"]);
        $this->assertEquals($result->customer_last_name, $data["customer_last_name"]);
        $this->assertEquals($result->customer_second_name, $data["customer_second_name"]);
        $this->assertEquals($result->phone, $data["phone"]);
        $this->assertTrue($result->status);
        $this->assertEquals($result->region_id, $data["region_id"]);
    }

    /** @test */
    public function success_update_from_import(): void
    {
        $service = app(ClientService::class);
        $region = Region::query()->first();
        $client = Client::query()->where('region_id', '!=', $region->id)->first();
        $data = [
            "id" => 232234,
            "customer_id" => "FD567",
            "company_name" => "test company",
            "customer_first_name" => "test first name",
            "customer_last_name" => "test last name",
            "customer_second_name" => "test second name",
            "phone" => "86657656978678",
            "status" => false,
            "created_at" => Carbon::now(),
            "region_id" => $region->id,
        ];

        $this->assertNotEquals($client->customer_id, $data["customer_id"]);
        $this->assertNotEquals($client->company_name, $data["company_name"]);
        $this->assertNotEquals($client->customer_first_name, $data["customer_first_name"]);
        $this->assertNotEquals($client->customer_last_name, $data["customer_last_name"]);
        $this->assertNotEquals($client->customer_second_name, $data["customer_second_name"]);
        $this->assertNotEquals($client->phone, $data["phone"]);
        $this->assertTrue($client->status);
        $this->assertNotEquals($client->region_id, $data["region_id"]);

        $result = $service->updateFromImport($data, $client);

        $this->assertEquals($result->customer_id, $data["customer_id"]);
        $this->assertEquals($result->company_name, $data["company_name"]);
        $this->assertEquals($result->customer_first_name, $data["customer_first_name"]);
        $this->assertEquals($result->customer_last_name, $data["customer_last_name"]);
        $this->assertEquals($result->customer_second_name, $data["customer_second_name"]);
        $this->assertEquals($result->phone, $data["phone"]);
        $this->assertFalse($result->status);
        $this->assertEquals($result->region_id, $data["region_id"]);
    }
}



