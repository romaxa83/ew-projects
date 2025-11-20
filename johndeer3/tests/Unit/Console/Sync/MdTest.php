<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\ModelDescription;
use App\Models\Version;
use App\Services\Import\ImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class MdTest extends TestCase
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

        $md_1 = ModelDescription::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($md_1->name, data_get($data, "0.name"));
        $this->assertEquals($md_1->status, data_get($data, "0.status"));
        $this->assertEquals($md_1->eg_jd_id, data_get($data, "0.equipment_group_id"));

        $md_2 = ModelDescription::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($md_2->name, data_get($data, "1.name"));
        $this->assertNotEquals($md_2->status, data_get($data, "1.status"));
        $this->assertEquals($md_2->eg_jd_id, data_get($data, "1.equipment_group_id"));

        $this->assertNull(Version::getVersionByAlias(Version::MODEL_DESCRIPTION));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_MD));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncModelDescription();

        $md_1->refresh();
        $md_2->refresh();

        $this->assertEquals($md_1->name, data_get($data, "0.name"));
        $this->assertEquals($md_1->status, data_get($data, "0.status"));
        $this->assertEquals($md_1->eg_jd_id, data_get($data, "0.equipment_group_id"));

        $this->assertEquals($md_2->name, data_get($data, "1.name"));
        $this->assertEquals($md_2->status, data_get($data, "1.status"));
        $this->assertEquals($md_2->eg_jd_id, data_get($data, "1.equipment_group_id"));

        $this->assertNotNull(Version::getVersionByAlias(Version::MODEL_DESCRIPTION));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_MD));
    }

    /** @test */
    public function success_add_new(): void
    {
        $data = self::data();
        $data[2] = [
            "id" => 99999,
            'status' => 1,
            'name' => "test",
            'equipment_group_id' => 1,
            "created_at" => Carbon::now()
        ];

        $md = ModelDescription::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($md);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncModelDescription();


        $md = ModelDescription::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($md->name, data_get($data, "2.name"));
        $this->assertEquals($md->status, data_get($data, "2.status"));
        $this->assertEquals($md->eg_jd_id, data_get($data, "2.equipment_group_id"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_MD, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncModelDescription();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_MD)->version);

    }

    public static function data():array
    {
        return [
            [
                "id" => 1,
                "name" => "AT FACTORY INSTALLED",
                "platform_id" => 1,
                "product_line_group_id" => 1,
                "product_line_id" => 1,
                "equipment_group_id" => 1,
                "composite_level_id" => 1,
                "estimate_description_id" => 1,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:52",
                "updated_at" => "2022-04-12 23:10:02",
            ],
            [
                "id" => 2,
                "name" => "AT ACTIVAT",
                "platform_id" => 1,
                "product_line_group_id" => 1,
                "product_line_id" => 1,
                "equipment_group_id" => 1,
                "composite_level_id" => 2,
                "estimate_description_id" => 2,
                "status" => 1,
                "created_at" => "2020-06-22 10:04:52",
                "updated_at" => "2022-04-12 23:10:02",
            ]
        ];
    }
}

