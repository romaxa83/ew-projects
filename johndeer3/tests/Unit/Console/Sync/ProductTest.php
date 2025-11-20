<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\Product;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductTest extends TestCase
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
        $data[1]['size_name'] = 23;
        $data[1]['model_description_id'] = 1;
        $data[1]['equipment_group_id'] = 2;
        $data[1]['manufacture_id'] = 2;
        $data[1]['size_parameter_id'] = 1;
        $data[1]['status'] = 0;
        $data[1]['type'] = 1;

        $model_1 = Product::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->jd_model_description_id, data_get($data, "0.model_description_id"));
        $this->assertEquals($model_1->jd_equipment_group_id, data_get($data, "0.equipment_group_id"));
        $this->assertEquals($model_1->jd_manufacture_id, data_get($data, "0.manufacture_id"));
        $this->assertEquals($model_1->jd_size_parameter_id, data_get($data, "0.size_parameter_id"));
        $this->assertEquals($model_1->size_name, data_get($data, "0.size_name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->type, data_get($data, "0.type"));

        $model_2 = Product::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertNotEquals($model_2->jd_model_description_id, data_get($data, "1.model_description_id"));
        $this->assertNotEquals($model_2->jd_equipment_group_id, data_get($data, "1.equipment_group_id"));
        $this->assertNotEquals($model_2->jd_manufacture_id, data_get($data, "1.manufacture_id"));
        $this->assertNotEquals($model_2->jd_size_parameter_id, data_get($data, "1.size_parameter_id"));
        $this->assertNotEquals($model_2->size_name, data_get($data, "1.size_name"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));
        $this->assertNotEquals($model_2->type, data_get($data, "1.type"));

        $this->assertNull(Version::getVersionByAlias(Version::PRODUCT));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_PRODUCT));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncProducts();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->jd_model_description_id, data_get($data, "0.model_description_id"));
        $this->assertEquals($model_1->jd_equipment_group_id, data_get($data, "0.equipment_group_id"));
        $this->assertEquals($model_1->jd_manufacture_id, data_get($data, "0.manufacture_id"));
        $this->assertEquals($model_1->jd_size_parameter_id, data_get($data, "0.size_parameter_id"));
        $this->assertEquals($model_1->size_name, data_get($data, "0.size_name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->type, data_get($data, "0.type"));

        $this->assertEquals($model_2->jd_model_description_id, data_get($data, "1.model_description_id"));
        $this->assertEquals($model_2->jd_equipment_group_id, data_get($data, "1.equipment_group_id"));
        $this->assertEquals($model_2->jd_manufacture_id, data_get($data, "1.manufacture_id"));
        $this->assertEquals($model_2->jd_size_parameter_id, data_get($data, "1.size_parameter_id"));
        $this->assertEquals($model_2->size_name, data_get($data, "1.size_name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->type, data_get($data, "1.type"));

        $this->assertNull(Version::getVersionByAlias(Version::PRODUCT));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_PRODUCT));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 9999,
            'size_name' => 1,
            'model_description_id' => 1,
            'equipment_group_id' => 1,
            'manufacture_id' => 1,
            'size_parameter_id' => 1,
            'status' => 1,
            'type' => 1,
        ];

        $md = Product::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($md);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncProducts();

        $model_1 = Product::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($model_1->jd_model_description_id, data_get($data, "2.model_description_id"));
        $this->assertEquals($model_1->jd_equipment_group_id, data_get($data, "2.equipment_group_id"));
        $this->assertEquals($model_1->jd_manufacture_id, data_get($data, "2.manufacture_id"));
        $this->assertEquals($model_1->jd_size_parameter_id, data_get($data, "2.size_parameter_id"));
        $this->assertEquals($model_1->size_name, data_get($data, "2.size_name"));
        $this->assertEquals($model_1->status, data_get($data, "2.status"));
        $this->assertEquals($model_1->type, data_get($data, "2.type"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_PRODUCT, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncProducts();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_PRODUCT)->version);
    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "size_name" => null,
                "platform_id" => 1,
                "product_line_group_id" => 1,
                "product_line_id" => 1,
                "equipment_group_id" => 1,
                "composite_level_id" => 1,
                "estimate_description_id" => 1,
                "model_description_id" => 1,
                "size_parameter_id" => null,
                "manufacture_id" => 1,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:54",
                "updated_at" => "2021-04-13 20:11:06",
                "type" => null,
                "active_model" => 1
            ], [
                "id" => 2,
                "size_name" => null,
                "platform_id" => 1,
                "product_line_group_id" => 1,
                "product_line_id" => 1,
                "equipment_group_id" => 1,
                "composite_level_id" => 2,
                "estimate_description_id" => 2,
                "model_description_id" => 2,
                "size_parameter_id" => null,
                "manufacture_id" => 1,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:54",
                "updated_at" => "2020-06-22 10:04:54",
                "type" => null,
                "active_model" => 1,
            ]
        ];
    }
}

