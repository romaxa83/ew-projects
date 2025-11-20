<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\Translate;
use App\Models\User\User;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class SmTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_add(): void
    {
        $data = self::data();

        $model_1 = User::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertNull($model_1);
        $model_2 = User::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertNull($model_2);

        $this->assertNull(Version::getVersionByAlias(Version::SALES_MANAGERS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_SM));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSM();

        $model_1 = User::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->profile->last_name, data_get($data, "0.last_name"));
        $this->assertEquals($model_1->profile->first_name, data_get($data, "0.first_name"));
        $this->assertEquals($model_1->email, data_get($data, "0.email"));
        $this->assertEquals($model_1->phone, data_get($data, "0.mobile_number"));
        $this->assertEquals($model_1->login, data_get($data, "0.login"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->lang, Translate::LANG_RU);
        $this->assertNotNull($model_1->password);
        $this->assertNull($model_1->nationality_id);
        $this->assertTrue($model_1->isSM());
        $this->assertNotNull($model_1->dealer);

        $model_2 = User::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($model_2->profile->last_name, data_get($data, "1.last_name"));
        $this->assertEquals($model_2->profile->first_name, data_get($data, "1.first_name"));
        $this->assertEquals($model_2->email, data_get($data, "1.email"));
        $this->assertEquals($model_2->phone, data_get($data, "1.mobile_number"));
        $this->assertEquals($model_2->login, data_get($data, "1.login"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->lang, Translate::LANG_RU);
        $this->assertNotNull($model_2->password);
        $this->assertNull($model_2->nationality_id);
        $this->assertTrue($model_2->isSM());
        $this->assertNotNull($model_2->dealer);

        $this->assertNull(Version::getVersionByAlias(Version::SALES_MANAGERS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_SM));
    }

    /** @test */
    public function success_edit(): void
    {
        $data = self::data();

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSM();

        $data[0]["last_name"] = "update_last_name";
        $data[0]["first_name"] = "update_first_name";
        $data[0]["email"] = "upadte@JohnDeere.com";
        $data[0]["mobile_number"] = "+380000000011";
        $data[0]["login"] = "test_update";
        $data[0]["status"] = 1;
        $data[0]["dealer_id"] = 9;

        $model_1 = User::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertNotEquals($model_1->profile->last_name, data_get($data, "0.last_name"));
        $this->assertNotEquals($model_1->profile->first_name, data_get($data, "0.first_name"));
        $this->assertNotEquals($model_1->email, data_get($data, "0.email"));
        $this->assertNotEquals($model_1->phone, data_get($data, "0.mobile_number"));
        $this->assertNotEquals($model_1->login, data_get($data, "0.login"));
        $this->assertNotEquals($model_1->status, data_get($data, "0.status"));
        $this->assertNotEquals($model_1->dealer->jd_id, data_get($data, "0.dealer_id"));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSM();

        $model_1->refresh();
        $this->assertEquals($model_1->profile->last_name, data_get($data, "0.last_name"));
        $this->assertEquals($model_1->profile->first_name, data_get($data, "0.first_name"));
        $this->assertEquals($model_1->email, data_get($data, "0.email"));
        $this->assertEquals($model_1->phone, data_get($data, "0.mobile_number"));
        $this->assertEquals($model_1->login, data_get($data, "0.login"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->dealer->jd_id, data_get($data, "0.dealer_id"));
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_SM, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncSM();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_SM)->version);
    }

    public static function data():array
    {
        return [
            [
                "id" => 16,
                "last_name" => "Bukatsela",
                "first_name" => "Volodymyr",
                "email" => "teti_100@ukr.net",
                "mobile_number" => "+380990955499",
                "login" => "13095Vordo",
                "status" => 0,
                "created_at" => "2020-07-01 10:51:31",
                "updated_at" => "2021-07-23 09:58:24",
                "dealer_id" => 10
            ],[
                "id" => 18,
                "last_name" => "Osmak",
                "first_name" => "Volodimir",
                "email" => "Vladimir.Osmak@jupiter9.com.ua",
                "mobile_number" => "+380500105220",
                "login" => "VladimirOsmak20",
                "status" => 1,
                "created_at" => "2020-07-01 11:04:54",
                "updated_at" => "2022-03-25 13:26:42",
                "dealer_id" => 9
            ]
        ];
    }
}

