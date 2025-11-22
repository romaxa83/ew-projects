<?php

namespace Tests\Feature\Queries\Admin;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Dealership\Dealership;
use App\Models\User\Car;
use App\Models\User\User;
use App\Types\Permissions;
use Arr;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class GetPaginatorUserCarsTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $brandRenault = Brand::query()->where('name', Brand::RENAULT)->first();
        $brandVolvo = Brand::query()->where('name', Brand::VOLVO)->first();

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user1->id)->setBrandId($brandVolvo->id)->create();
        $carBuilder->setUserId($user1->id)->setBrandId($brandRenault->id)->create();
        // second user
        $user2 = $userBuilder->setPhone('89999999999')->setEmail('some@gmail.com')->create();
        $carBuilder->setUserId($user2->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user2->id)->setBrandId($brandVolvo->id)->create();
        $carBuilder->setUserId($user2->id)->setBrandId($brandRenault->id)->create();

        $response = $this->graphQL(self::getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.cars');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertCount(6, $responseData['data']);

        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('number', $responseData['data'][0]);
        $this->assertArrayHasKey('vin', $responseData['data'][0]);
        $this->assertArrayHasKey('year', $responseData['data'][0]);
        $this->assertArrayHasKey('brand', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]['brand']);
        $this->assertArrayHasKey('model', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]['model']);

        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['paginatorInfo']['count'], 6);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], 1);
        $this->assertFalse($responseData['paginatorInfo']['hasMorePages']);
        $this->assertEquals($responseData['paginatorInfo']['lastPage'], 1);
    }

    /** @test */
    public function get_car_by_dealership_as_admin()
    {
        $dealership = Dealership::find(1);
        $dealershipBrandName = strtolower($dealership->brand->name);

        $brandRenault = Brand::query()->where('name', Brand::RENAULT)->first();
        $brandVolvo = Brand::query()->where('name', Brand::VOLVO)->first();

        $admin = $this->adminBuilder()
            ->withDealership($dealership)
            ->createRoleWithPerms([Permissions::USER_CAR_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();

        $carBuilder = $this->carBuilder();
        $carBuilder->setUserId($user->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user->id)->setBrandId($brandVolvo->id)->create();


        $this->assertEquals($dealershipBrandName, Brand::RENAULT);
        $this->assertEquals($admin->dealership->id, $dealership->id);
        $this->assertEquals($dealership->brand_id, $brandRenault->id);

        $res = $this->graphQL(self::getQueryStr());

        $resData = $res->json('data.cars');

        $this->assertEquals(2, Arr::get($resData, 'paginatorInfo.count'));

        foreach ($resData['data'] as $item){
            $this->assertEquals($brandRenault->name, Arr::get($item, 'brand.name'));
        }
    }

    /** @test */
    public function get_car__as_admin()
    {
        $dealership = Dealership::find(1);
        $dealershipBrandName = strtolower($dealership->brand->name);

        $brandRenault = Brand::query()->where('name', Brand::RENAULT)->first();
        $brandVolvo = Brand::query()->where('name', Brand::VOLVO)->first();

        $admin = $this->adminBuilder()
            ->withDealership($dealership)
            ->createRoleWithPerms([Permissions::USER_CAR_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();

        $carBuilder = $this->carBuilder();
        $carBuilder->setUserId($user->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user->id)->setBrandId($brandRenault->id)->create();
        $carBuilder->setUserId($user->id)->setBrandId($brandVolvo->id)->create();


        $this->assertEquals($dealershipBrandName, Brand::RENAULT);
        $this->assertEquals($admin->dealership->id, $dealership->id);
        $this->assertEquals($dealership->brand_id, $brandRenault->id);

        $res = $this->graphQL(self::getQueryStr());

        $resData = $res->json('data.cars');

        $this->assertEquals(2, Arr::get($resData, 'paginatorInfo.count'));

        foreach ($resData['data'] as $item){
            $this->assertEquals($brandRenault->name, Arr::get($item, 'brand.name'));
        }
    }

    /** @test */
    public function get_by_vin()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $vin = '1111111111111';
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setVin($vin)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();

        $total = Car::query()->where('vin', $vin)->count();
        $response = $this->graphQL(self::getQueryStrVinSearch($vin));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_number()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $number = '1111111111111';
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setNumber($number)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();

        $total = Car::query()->where('number', $number)->count();

        $response = $this->graphQL(self::getQueryStrNumberSearch($number));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_username()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $userName = 'renbo';
        // first user
        $user1 = $userBuilder->setName($userName)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        // second user
        $user2 = User::factory()->create();
        $carBuilder->setUserId($user2->id)->create();
        $carBuilder->setUserId($user2->id)->create();

        $total = User::query()->with('cars')->where('name', $userName)->first()->cars()->count();

        $response = $this->graphQL(self::getQueryStrUserNameSearch($userName));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_username_more_user()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $userName = 'renbo';
        // first user
        $user1 = $userBuilder->setName($userName)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        // second user
        $user2 = User::factory()->create(['name' => 'some' . $userName]);
        $carBuilder->setUserId($user2->id)->create();
        $carBuilder->setUserId($user2->id)->create();

        $response = $this->graphQL(self::getQueryStrUserNameSearch($userName));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), 5);
    }

    /** @test */
    public function get_by_user_phone()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $userPhone = '89999999991';
        // first user
        $user1 = $userBuilder->setPhone($userPhone)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        // second user
        $user2 = $userBuilder->setPhone('89999999999')->setEmail('some@gmail.com')->create();
        $carBuilder->setUserId($user2->id)->create();
        $carBuilder->setUserId($user2->id)->create();

        $total = User::query()->with('cars')->where('phone', $userPhone)->first()->cars()->count();

        $response = $this->graphQL(self::getQueryStrUserPhoneSearch($userPhone));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_brand_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();

        $brand = Brand::where('id', 1)->first();

        $carBuilder->setUserId($user1->id)->setBrandId($brand->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();

        $total = Car::query()->where('brand_id', $brand->id)->count();

        $response = $this->graphQL(self::getQueryStrBrandIdSearch($brand->id));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_model_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();

        $model = Model::where('id', 10)->first();

        $carBuilder->setUserId($user1->id)->setModelId($model->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();

        $total = Car::query()->where('model_id', $model->id)->count();

        $response = $this->graphQL(self::getQueryStrModelIdSearch($model->id));

        $this->assertEquals($response->json('data.cars.paginatorInfo.total'), $total);
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            cars {
                data{
                    id
                    number
                    vin
                    year
                    brand {
                        name
                    }
                    model {
                        name
                    }
                }
                paginatorInfo {
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }',
        );
    }

    public static function getQueryStrVinSearch(string $vin): string
    {
        return  sprintf('{
            cars (vin: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
        $vin
        );
    }

    public static function getQueryStrNumberSearch(string $search): string
    {
        return  sprintf('{
            cars (number: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $search
        );
    }

    public static function getQueryStrUserNameSearch(string $search): string
    {
        return  sprintf('{
            cars (userName: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $search
        );
    }

    public static function getQueryStrUserPhoneSearch(string $search): string
    {
        return  sprintf('{
            cars (userPhone: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $search
        );
    }

    public static function getQueryStrBrandIdSearch($brandId): string
    {
        return  sprintf('{
            cars (brandId: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $brandId
        );
    }

    public static function getQueryStrModelIdSearch($modelId): string
    {
        return  sprintf('{
            cars (modelId: "%s") {
                data{
                    id
                    number
                    vin
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $modelId
        );
    }
}

