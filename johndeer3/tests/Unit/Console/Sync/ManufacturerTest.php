<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\Manufacturer;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class ManufacturerTest extends TestCase
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
        $data[1]['position'] = 3;
        $data[1]['name'] = "test";
        $data[1]['relationship'] = 1;

        $model_1 = Manufacturer::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->position, data_get($data, "0.position"));
        $this->assertEquals($model_1->is_partner_jd, data_get($data, "0.relationship"));

        $model_2 = Manufacturer::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertNotEquals($model_2->name, data_get($data, "1.name"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));
        $this->assertNotEquals($model_2->position, data_get($data, "1.position"));
        $this->assertNotEquals($model_2->is_partner_jd, data_get($data, "1.relationship"));

        $this->assertNull(Version::getVersionByAlias(Version::MANUFACTURER));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_MANUFACTURE));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncManufacturer();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->position, data_get($data, "0.position"));
        $this->assertEquals($model_1->is_partner_jd, data_get($data, "0.relationship"));

        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->position, data_get($data, "1.position"));
        $this->assertEquals($model_2->is_partner_jd, data_get($data, "1.relationship"));

        $this->assertNotNull(Version::getVersionByAlias(Version::MANUFACTURER));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_MANUFACTURE));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 9999,
            'status' => 1,
            'name' => "test",
            'position' => 1,
            "relationship" => 1
        ];

        $md = Manufacturer::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($md);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncManufacturer();

        $md = Manufacturer::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($md->name, data_get($data, "2.name"));
        $this->assertEquals($md->status, data_get($data, "2.status"));
        $this->assertEquals($md->position, data_get($data, "2.position"));
        $this->assertEquals($md->is_partner_jd, data_get($data, "2.relationship"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_MANUFACTURE, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncManufacturer();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_MANUFACTURE)->version);

    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "name" => "John Deere",
                "status" => 1,
                "created_at" => "2020-06-22 10:04:51",
                "updated_at" => "2020-07-17 10:10:24",
                "relationship" => 1,
                "position" => 1
            ],
            [
                "id" => 2,
                "name" => "VERSATILE",
                "status" => 1,
                "created_at" => "2020-06-22 10:04:51",
                "updated_at" => "2020-06-22 10:04:51",
                "relationship" => 2,
                "position" => 500
            ]
        ];
    }
}


