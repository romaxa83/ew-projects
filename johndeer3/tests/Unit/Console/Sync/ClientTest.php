<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\Client;
use App\Models\Version;
use App\Services\Import\ImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        $data = self::data();
        $data[1]['status'] = 0;
        $data[1]['phone'] = "+380681209999";
        $data[1]['customer_first_name'] = "test_first";
        $data[1]['customer_last_name'] = "test_last";
        $data[1]['customer_second_name'] = "test_second";

        $model_1 = Client::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->customer_id, data_get($data, "0.customer_id"));
        $this->assertEquals($model_1->company_name, data_get($data, "0.company_name"));
        $this->assertEquals($model_1->customer_first_name, data_get($data, "0.customer_first_name"));
        $this->assertEquals($model_1->customer_last_name, data_get($data, "0.customer_last_name"));
        $this->assertEquals($model_1->customer_second_name, data_get($data, "0.customer_second_name"));
        $this->assertEquals($model_1->phone, data_get($data, "0.phone"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->region_id, data_get($data, "0.region_id"));

        $model_2 = Client::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($model_2->customer_id, data_get($data, "1.customer_id"));
        $this->assertEquals($model_2->company_name, data_get($data, "1.company_name"));
        $this->assertNotEquals($model_2->customer_first_name, data_get($data, "1.customer_first_name"));
        $this->assertNotEquals($model_2->customer_last_name, data_get($data, "1.customer_last_name"));
        $this->assertNotEquals($model_2->customer_second_name, data_get($data, "1.customer_second_name"));
        $this->assertNotEquals($model_2->phone, data_get($data, "1.phone"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->region_id, data_get($data, "1.region_id"));

        $this->assertNull(Version::getVersionByAlias(Version::CLIENTS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_CLIENT));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncClients();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->customer_id, data_get($data, "0.customer_id"));
        $this->assertEquals($model_1->company_name, data_get($data, "0.company_name"));
        $this->assertEquals($model_1->customer_first_name, data_get($data, "0.customer_first_name"));
        $this->assertEquals($model_1->customer_last_name, data_get($data, "0.customer_last_name"));
        $this->assertEquals($model_1->customer_second_name, data_get($data, "0.customer_second_name"));
        $this->assertEquals($model_1->phone, data_get($data, "0.phone"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->region_id, data_get($data, "0.region_id"));

        $this->assertEquals($model_2->customer_id, data_get($data, "1.customer_id"));
        $this->assertEquals($model_2->company_name, data_get($data, "1.company_name"));
        $this->assertEquals($model_2->customer_first_name, data_get($data, "1.customer_first_name"));
        $this->assertEquals($model_2->customer_last_name, data_get($data, "1.customer_last_name"));
        $this->assertEquals($model_2->customer_second_name, data_get($data, "1.customer_second_name"));
        $this->assertEquals($model_2->phone, data_get($data, "1.phone"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->region_id, data_get($data, "1.region_id"));

        $this->assertNotNull(Version::getVersionByAlias(Version::CLIENTS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_CLIENT));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 999999,
            "customer_id" => "3851254_test",
            "company_name" => "test_company",
            "customer_first_name" => "test_first_name",
            "customer_last_name" => "test_last_name",
            "customer_second_name" => "test_second_name",
            "phone" => "+3804491311111",
            "status" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
            "region_id" => 1
        ];

        $md = Client::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($md);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncClients();

        $model = Client::query()->where('jd_id', data_get($data, "2.id"))->first();

        $this->assertEquals($model->customer_id, data_get($data, "2.customer_id"));
        $this->assertEquals($model->company_name, data_get($data, "2.company_name"));
        $this->assertEquals($model->customer_first_name, data_get($data, "2.customer_first_name"));
        $this->assertEquals($model->customer_last_name, data_get($data, "2.customer_last_name"));
        $this->assertEquals($model->customer_second_name, data_get($data, "2.customer_second_name"));
        $this->assertEquals($model->phone, data_get($data, "2.phone"));
        $this->assertEquals($model->status, data_get($data, "2.status"));
        $this->assertEquals($model->region_id, data_get($data, "2.region_id"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_CLIENT, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncClients();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_CLIENT)->version);

    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "customer_id" => "3851254",
                "company_name" => "СГ ТОВ \"МАКАРІВСЬКЕ\"",
                "customer_first_name" => "Юрій",
                "customer_last_name" => "Салата",
                "customer_second_name" => "Іванович",
                "phone" => "+380449132251",
                "status" => 1,
                "created_at" => "2020-06-23 15:10:23",
                "updated_at" => "2021-04-29 17:41:08",
                "region_id" => 1
            ],
            [
                "id" => 2,
                "customer_id" => "3545018",
                "company_name" => "ТОВ Ромашки",
                "customer_first_name" => "Василь",
                "customer_last_name" => "Бурченко",
                "customer_second_name" => "Миколайович",
                "phone" => "+380681202223",
                "status" => 1,
                "created_at" => "2020-06-23 15:10:23",
                "updated_at" => "2021-01-20 12:58:10",
                "region_id" => 1
            ]
        ];
    }
}


