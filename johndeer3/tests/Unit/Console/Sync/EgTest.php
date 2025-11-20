<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\EquipmentGroup;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class EgTest extends TestCase
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

        $model_1 = EquipmentGroup::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $model_2 = EquipmentGroup::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNull(Version::getVersionByAlias(Version::EQUIPMENT_GROUP));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_EG));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncEquipmentGroup();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNotNull(Version::getVersionByAlias(Version::EQUIPMENT_GROUP));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_EG));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 99999,
            'status' => 1,
            'name' => "test"
        ];

        $model = EquipmentGroup::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($model);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncEquipmentGroup();

        $model = EquipmentGroup::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($model->name, data_get($data, "2.name"));
        $this->assertEquals($model->status, data_get($data, "2.status"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_EG, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncEquipmentGroup();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_EG)->version);

    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "name" => "AMS",
                "platform_id" => 1,
                "product_line_group_id" => 1,
                "product_line_id" => 1,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:51",
                "updated_at" => "2020-06-22 10:04:51"
            ],
            [
                "id" => 2,
                "name" => "Round Balers",
                "platform_id" => 2,
                "product_line_group_id" => 2,
                "product_line_id" => 2,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:51",
                "updated_at" => "2020-06-22 10:04:51"
            ]
        ];
    }
}
