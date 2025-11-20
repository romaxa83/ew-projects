<?php

namespace Tests\Unit\Console\Sync;

use App\Console\Commands\Worker\SyncJD;
use App\Models\JD\Dealer;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\Version;
use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class DealerTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $data = self::data();
        $data[1]['status'] = 0;

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();
        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->jd_jd_id, data_get($data, "0.jd_id"));
        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));

        $nameCountry = explode('-', data_get($data, "0.country"));
        $country_1 = Nationality::query()->where('name', trim(last($nameCountry)))->first();
        $this->assertNotNull($country_1);
        $this->assertEquals($model_1->nationality_id, $country_1->id);

        $model_2 = Dealer::query()->where('jd_id', data_get($data, "1.id"))->first();
        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->jd_jd_id, data_get($data, "1.jd_id"));
        $this->assertEquals($model_2->country, data_get($data, "1.country"));
        $this->assertNotEquals($model_2->status, data_get($data, "1.status"));

        $nameCountry = explode('-', data_get($data, "1.country"));
        $country_2 = Nationality::query()->where('name', trim(last($nameCountry)))->first();
        $this->assertNotNull($country_2);
        $this->assertEquals($model_2->nationality_id, $country_2->id);

        $this->assertNull(Version::getVersionByAlias(Version::DEALERS));
        $this->assertNull(Version::getVersionByAlias(Version::IMPORT_DEALER));

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();
        $model_2->refresh();

        $this->assertEquals($model_1->name, data_get($data, "0.name"));
        $this->assertEquals($model_1->jd_jd_id, data_get($data, "0.jd_id"));
        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertEquals($model_1->status, data_get($data, "0.status"));
        $this->assertEquals($model_1->nationality_id, $country_1->id);

        $this->assertEquals($model_2->name, data_get($data, "1.name"));
        $this->assertEquals($model_2->status, data_get($data, "1.status"));
        $this->assertEquals($model_2->jd_jd_id, data_get($data, "1.jd_id"));
        $this->assertEquals($model_2->country, data_get($data, "1.country"));
        $this->assertEquals($model_2->nationality_id, $country_2->id);

        $this->assertNotNull(Version::getVersionByAlias(Version::DEALERS));
        $this->assertNotNull(Version::getVersionByAlias(Version::IMPORT_DEALER));
    }

    /** @test */
    public function success_change_country(): void
    {
        $country = Nationality::query()->first();
        $data = self::data();
        $data[0]['country'] = "{$country->alias} - {$country->name}";

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();

        $this->assertNotEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNotEquals($model_1->nationality_id, $country->id);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();

        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertEquals($model_1->nationality_id, $country->id);
    }

    /** @test */
    public function success_not_found_country(): void
    {
        $data = self::data();
        $data[0]['country'] = "unknow - unknow";

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();

        $this->assertNotEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNotNull($model_1->nationality_id);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();

        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNull($model_1->nationality_id);
    }

    /** @test */
    public function success_country_as_null(): void
    {
        $data = self::data();
        $data[0]['country'] = null;

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();

        $this->assertNotEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNotNull($model_1->nationality_id);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();

        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNull($model_1->nationality_id);
    }

    /** @test */
    public function success_country_as_isset(): void
    {
        $data = self::data();
        unset($data[0]['country']);

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();

        $this->assertNotEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNotNull($model_1->nationality_id);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();

        $this->assertEquals($model_1->country, data_get($data, "0.country"));
        $this->assertNull($model_1->nationality_id);
    }

    /** @test */
    public function success_change_status_dealer_ps(): void
    {
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $data = self::data();
        $data[0]['status'] = 0;

        $model_1 = Dealer::query()->where('jd_id', data_get($data, "0.id"))->first();

        $user_1 = $this->userBuilder->setRole($role_ps)->setDealer($model_1)->create();
        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($model_1)->create();
        $user_3 = $this->userBuilder->setRole($role_tm)->setDealer($model_1)->create();

        $this->assertTrue($user_1->status);
        $this->assertTrue($user_1->isPS());
        $this->assertTrue($user_2->status);
        $this->assertTrue($user_2->isPS());
        $this->assertTrue($user_3->status);
        $this->assertTrue($user_3->isTM());

        $this->assertNotEmpty($model_1->users_ps);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model_1->refresh();
        $user_1->refresh();
        $user_2->refresh();
        $user_3->refresh();

        $this->assertFalse($model_1->status);
        $this->assertFalse($user_1->status);
        $this->assertFalse($user_2->status);
        $this->assertTrue($user_3->status);
    }

    /** @test */
    public function success_add_new(): void
    {
        $country = Nationality::query()->first();
        $data = self::data();
        $data[2] = [
            "id" => 99999,
            'status' => 1,
            'name' => "test",
            'jd_id' => "test",
            'country' => "{$country->alias} - {$country->name}"
        ];

        $model = Dealer::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertNull($model);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $model = Dealer::query()->where('jd_id', data_get($data, "2.id"))->first();
        $this->assertEquals($model->name, data_get($data, "2.name"));
        $this->assertEquals($model->status, data_get($data, "2.status"));
        $this->assertEquals($model->jd_jd_id, data_get($data, "2.jd_id"));
        $this->assertEquals($model->country, data_get($data, "2.country"));
        $this->assertEquals($model->nationality_id, $country->id);
    }

    /** @test */
    public function success_not_change_hash(): void
    {
        $data = self::data();

        $hash = Version::getHash($data);

        Version::setVersion(Version::IMPORT_DEALER, $hash);

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        app(SyncJD::class, [$service])->syncDealers();

        $this->assertEquals($hash, Version::getVersionByAlias(Version::IMPORT_DEALER)->version);

    }

    public static function data():array
    {
        return [
            [
                "id" => 6,
                "jd_id" => "482GP1",
                "name" => "Agristar",
                "status" => 1,
                "country" => "UA - Ukraine",
                "created_at" => "2020-06-22 10:48:37",
                "updated_at" => "2022-04-01 13:40:34"
            ],
            [
                "id" => 7,
                "jd_id" => "482UB0",
                "name" => "Agrosem",
                "status" => 1,
                "country" => "UA - Ukraine",
                "created_at" => "2020-06-22 10:50:47",
                "updated_at" => "2022-02-16 10:18:40"
            ]
        ];
    }
}
