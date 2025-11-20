<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\Region;
use App\Models\Version;
use App\Services\Import\ImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class RegionTest extends TestCase
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

        $model_1 = Region::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $model_2 = Region::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNull(Version::getVersionByAlias(Version::REGIONS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_REGION));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncRegions();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNull(Version::getVersionByAlias(Version::REGIONS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_REGION));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 99999,
            'status' => 1,
            'name' => "test",
            "created_at" => Carbon::now()
        ];

        $model = Region::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($model);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncRegions();

        $model = Region::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($model->name, data_get($data, "2.name"));
        $this->assertEquals($model->status, data_get($data, "2.status"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_REGION, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncRegions();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_REGION)->version);
    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "name" => "Київська",
                "status" => 1,
                "created_at" => "2020-06-22 10:03:22",
                "updated_at" => "2020-06-22 10:03:22"
            ],
            [
                "id" => 2,
                "name" => "Черкаська",
                "status" => 1,
                "created_at" => "2020-06-22 10:03:22",
                "updated_at" => "2020-06-22 10:03:22"
            ]
        ];
    }
}


