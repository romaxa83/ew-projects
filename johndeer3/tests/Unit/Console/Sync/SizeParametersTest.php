<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\SizeParameters;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class SizeParametersTest extends TestCase
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
        $data[1]['name'] = "test";

        $model_1 = SizeParameters::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $model_2 = SizeParameters::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertNotEquals($model_2->name, data_get($data, "1.name"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNull(Version::getVersionByAlias(Version::SIZE_PARAMETERS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_SP));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSizeParameters();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));

        $this->assertNull(Version::getVersionByAlias(Version::SIZE_PARAMETERS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_SP));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 9999,
            'status' => 1,
            'name' => "test",
        ];

        $md = SizeParameters::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($md);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSizeParameters();

        $md = SizeParameters::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($md->name, data_get($data, "2.name"));
        $this->assertEquals($md->status, data_get($data, "2.status"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::SIZE_PARAMETERS, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSizeParameters();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::SIZE_PARAMETERS)->version);
    }

    public static function data():array
    {
        return [
            [
                "id" => 2,
                "name" => "h.p.",
                "status" => 1,
                "created_at" => "2020-06-22 10:04:54",
                "updated_at" => "2020-06-22 10:04:54"
            ], [
                "id" => 3,
                "name" => "kg",
                "status" => 1,
                "created_at" => "2020-06-22 10:04:54",
                "updated_at" => "2020-06-22 10:04:54"
            ]
        ];
    }
}
