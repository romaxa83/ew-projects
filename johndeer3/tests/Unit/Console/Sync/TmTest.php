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

class TmTest extends TestCase
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

        $this->assertNull(Version::getVersionByAlias(Version::TERRITORIAL_MANAGERS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_TM));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncTM();

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
        $this->assertTrue($model_1->isTM());
        $this->assertNotEmpty($model_1->dealers);
        $this->assertCount(count(data_get($data, "0.dealer_ids")), $model_1->dealers);

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
        $this->assertTrue($model_2->isTM());
        $this->assertNotEmpty($model_2->dealers);
        $this->assertCount(count(data_get($data, "1.dealer_ids")), $model_2->dealers);

        $this->assertNull(Version::getVersionByAlias(Version::TERRITORIAL_MANAGERS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_TM));
    }

    /** @test */
    public function success_edit(): void
    {
        $data = self::data();

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncTM();

        $data[0]["last_name"] = "update_last_name";
        $data[0]["first_name"] = "update_first_name";
        $data[0]["email"] = "upadte@JohnDeere.com";
        $data[0]["mobile_number"] = "+380000000011";
        $data[0]["login"] = "test_update";
        $data[0]["status"] = 0;
        $data[0]["dealer_ids"] = [];

        $model_1 = User::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertNotEquals($model_1->profile->last_name, data_get($data, "0.last_name"));
        $this->assertNotEquals($model_1->profile->first_name, data_get($data, "0.first_name"));
        $this->assertNotEquals($model_1->email, data_get($data, "0.email"));
        $this->assertNotEquals($model_1->phone, data_get($data, "0.mobile_number"));
        $this->assertNotEquals($model_1->login, data_get($data, "0.login"));
        $this->assertNotEquals($model_1->status, data_get($data, "0.status"));
        $this->assertNotEmpty($model_1->dealers);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncTM();

        $model_1->refresh();
        $this->assertEquals($model_1->profile->last_name, data_get($data, "0.last_name"));
        $this->assertEquals($model_1->profile->first_name, data_get($data, "0.first_name"));
        $this->assertEquals($model_1->email, data_get($data, "0.email"));
        $this->assertEquals($model_1->phone, data_get($data, "0.mobile_number"));
        $this->assertEquals($model_1->login, data_get($data, "0.login"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEmpty($model_1->dealers);
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_TM, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncTM();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_TM)->version);
    }

    public static function data():array
    {
        return [
            [
                "id" => 4,
                "last_name" => "TM",
                "first_name" => "Test",
                "email" => "oldtm@JohnDeere.com",
                "mobile_number" => "+380000000000",
                "login" => "3205974gyr",
                "status" => 1,
                "created_at" => "2020-06-22 10:39:49",
                "updated_at" => "2021-08-04 14:10:15",
                "dealer_ids" => [40]
            ],[
                "id" => 5,
                "last_name" => "Mustafaev",
                "first_name" => "Oleksandr",
                "email" => "MustafaevOleksandr@JohnDeere.com",
                "mobile_number" => "+380504357296",
                "login" => "TMom29426",
                "status" => 1,
                "created_at" => "2020-06-22 10:41:18",
                "updated_at" => "2021-08-03 15:42:52",
                "dealer_ids" => [7, 9, 11]
            ]
        ];
    }
}
