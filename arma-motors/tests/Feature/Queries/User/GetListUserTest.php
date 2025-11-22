<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\User;
use App\Types\Permissions;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class GetListUserTest extends TestCase
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
        $total = 20;
        User::factory()->count($total)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.users');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertCount(10, $responseData['data']);

        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('email', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]);
        $this->assertArrayHasKey('phone', $responseData['data'][0]);
        $this->assertArrayHasKey('lang', $responseData['data'][0]);
        $this->assertArrayHasKey('egrpoy', $responseData['data'][0]);
        $this->assertArrayHasKey('createdAt', $responseData['data'][0]);
        $this->assertArrayHasKey('updatedAt', $responseData['data'][0]);
        $this->assertArrayHasKey('locale', $responseData['data'][0]);
        $this->assertArrayHasKey('locale', $responseData['data'][0]['locale']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['locale']);

        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['paginatorInfo']['total'], $total);
        $this->assertEquals($responseData['paginatorInfo']['count'], 10);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], 1);
        $this->assertTrue($responseData['paginatorInfo']['hasMorePages']);
        $this->assertEquals($responseData['paginatorInfo']['lastPage'], 2);
    }

    /** @test */
    public function get_success_with_params()
    {
        $total = 20;
        User::factory()->count($total)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $params = [
            'first' => 3,
            'page' => 3
        ];

        $response = $this->graphQL($this->getQueryStrWithParams($params))
            ->assertOk();

        $responseData = $response->json('data.users');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertCount($params['first'], $responseData['data']);

        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertEquals($responseData['paginatorInfo']['total'], $total);
        $this->assertEquals($responseData['paginatorInfo']['count'], $params['first']);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], $params['page']);
        $this->assertTrue($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function get_success_order_by_id()
    {
        User::factory()->count(5)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('id'));

        $responseData = $response->json('data.users');
        $firstElement = current($responseData['data']);

        $response = $this->graphQL($this->getQueryStrOrder('id', 'DESC'));

        $responseData = $response->json('data.users');
        $newFirstElement = current($responseData['data']);
        $newLastElement = last($responseData['data']);

        $this->assertNotEquals($firstElement['id'], $newFirstElement['id']);
        $this->assertEquals($firstElement['id'], $newLastElement['id']);
    }

    /** @test */
    public function get_success_order_by_phone()
    {
        User::factory()->count(5)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('phone'));

        $responseData = $response->json('data.users');
        $firstElement = current($responseData['data']);

        $response = $this->graphQL($this->getQueryStrOrder('phone', 'DESC'));

        $responseData = $response->json('data.users');
        $newFirstElement = current($responseData['data']);

        $this->assertNotEquals($firstElement['id'], $newFirstElement['id']);
    }

    /** @test */
    public function get_success_without_super_admin()
    {
        $admin = Admin::superAdmin()->first();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.users');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertEmpty($responseData['data']);

        $this->assertEquals($responseData['paginatorInfo']['count'], 0);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], 1);
        $this->assertEquals($responseData['paginatorInfo']['lastPage'], 1);
        $this->assertFalse($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function get_by_username()
    {
        $serachStr = 'rembo';
        User::factory()->count(5)->create();
        User::factory()->create(['name' => $serachStr]);

        $total = User::query()->where('name','like', '%' . $serachStr . '%')->count();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrNameSearch($serachStr))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_username_more()
    {
        $serachStr = 'rembo';
        User::factory()->count(5)->create();
        User::factory()->create(['name' => $serachStr]);
        User::factory()->create(['name' => 'som '. $serachStr . ' tes']);
        User::factory()->create(['name' => 'som '. $serachStr]);
        User::factory()->create(['name' => $serachStr . ' tes']);

        $total = User::query()->where('name','like', '%' . $serachStr . '%')->count();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrNameSearch($serachStr))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_phone()
    {
        $serachStr = '380954445566';
        User::factory()->count(5)->create();
        User::factory()->create(['phone' => new Phone($serachStr)]);

        $total = User::query()->where('phone', $serachStr)->count();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrPhoneSearch($serachStr))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_has_new_phone()
    {
        $serachStr = '3809544455';
        User::factory()->count(5)->create();
        User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'phone_edit_at' => Carbon::now()
        ]);
        User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'phone_edit_at' => Carbon::now()
        ]);

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $total = User::query()->where('phone_edit_at', '!=', null)->count();
        $response = $this->graphQL($this->getQueryStrByNewPhone(true))->assertOk();
        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);

        $total = User::query()->where('phone_edit_at', '=', null)->count();
        $response = $this->graphQL($this->getQueryStrByNewPhone(false))->assertOk();
        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_phone_wrong()
    {
        $serachStr = '380954445566';
        $wrong = '3809';
        User::factory()->count(5)->create();
        User::factory()->create(['phone' => new Phone($serachStr)]);

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrPhoneSearch($wrong));

        $this->assertEmpty($response->json('data.users.data'));
        $this->assertEquals($response->json('data.users.paginatorInfo.total'), 0);
    }

    /** @test */
    public function get_by_car_brand()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();
        $brand1 = Brand::where('id', 1)->first();
        $carBuilder->setUserId($user1->id)->setBrandId($brand1->id)->create();
        //second user
        $user2 = $userBuilder->setPhone('09887766566')->setEmail('some@test.com')->create();
        $brand2 = Brand::where('id', 2)->first();
        $carBuilder->setUserId($user2->id)->setBrandId($brand2->id)->create();

        $id = $brand1->id;
        $total = User::query()->with('cars')
            ->whereHas('cars', function($q) use ($id){
                $q->where('brand_id', $id);
            })->count();

        $this->assertNotEquals(0, $total);

        $response = $this->graphQL($this->getQueryStrBrandSearch($id))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_car_brand_more()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $brand = Brand::where('id', 1)->first();
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setBrandId($brand->id)->create();
        //second user
        $user2 = $userBuilder->setPhone('09887766566')->setEmail('some@test.com')->create();
        $carBuilder->setUserId($user2->id)->setBrandId($brand->id)->create();

        $id = $brand->id;
        $total = User::query()->with('cars')
            ->whereHas('cars', function($q) use ($id){
                $q->where('brand_id', $id);
            })->count();

        $this->assertNotEquals(0, $total);

        $response = $this->graphQL($this->getQueryStrBrandSearch($id))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_car_model()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();
        $model1 = Model::where('id', 10)->first();
        $carBuilder->setUserId($user1->id)->setModelId($model1->id)->create();
        //second user
        $user2 = $userBuilder->setPhone('09887766566')->setEmail('some@test.com')->create();
        $model2 = Model::where('id', 12)->first();
        $carBuilder->setUserId($user2->id)->setModelId($model2->id)->create();

        $id = $model1->id;
        $total = User::query()->with('cars')
            ->whereHas('cars', function($q) use ($id){
                $q->where('model_id', $id);
            })->count();

        $this->assertNotEquals(0, $total);

        $response = $this->graphQL($this->getQueryStrModelSearch($id))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_car_model_more()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $model = Model::where('id', 10)->first();
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setModelId($model->id)->create();
        //second user
        $user2 = $userBuilder->setPhone('09887766566')->setEmail('some@test.com')->create();
        $carBuilder->setUserId($user2->id)->setModelId($model->id)->create();

        $id = $model->id;
        $total = User::query()->with('cars')
            ->whereHas('cars', function($q) use ($id){
                $q->where('model_id', $id);
            })->count();

        $this->assertNotEquals(0, $total);

        $response = $this->graphQL($this->getQueryStrModelSearch($id))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_car_year()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $year1 = '2000';
        $year2 = '2022';

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        $model = Model::where('id', 10)->first();
        // first user
        $user1 = $userBuilder->create();
        $carBuilder->setUserId($user1->id)->setModelId($model->id)->setYear($year1)->create();
        //second user
        $user2 = $userBuilder->setPhone('09887766566')->setEmail('some@test.com')->create();
        $carBuilder->setUserId($user2->id)->setModelId($model->id)->setYear($year2)->create();

        $id = $model->id;

        $total = User::query()->with('cars')
            ->whereHas('cars', function($q) use ($year1){
                $q->where('year', $year1);
            })->count();

        $this->assertEquals(1, $total);

        $response = $this->graphQL($this->getQueryStrCarYearSearch($year1))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function get_by_phone_with_not_correct()
    {
        $serachStr = '+38(095)444-55-66';
        $correctPhoneStr = '380954445566';
        User::factory()->count(5)->create();
        User::factory()->create(['phone' => new Phone($serachStr)]);

        $total = User::query()->where('phone', $correctPhoneStr)->count();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrPhoneSearch($serachStr))->assertOk();

        $this->assertEquals($response->json('data.users.paginatorInfo.total'), $total);
    }

    /** @test */
    public function sort_new_phone_at()
    {
        $serachStr = '3809544455';
        $user1 = User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'phone_edit_at' => Carbon::now()
        ]);
        $user2 = User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'phone_edit_at' => Carbon::now()->addMinutes(22)
        ]);

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('PHONE_EDIT_AT', 'ASC'));

        $this->assertEquals($response->json('data.users.data.0.id'), $user1->id);

        $response = $this->graphQL($this->getQueryStrOrder('PHONE_EDIT_AT', 'DESC'));

        $this->assertEquals($response->json('data.users.data.0.id'), $user2->id);
    }

    /** @test */
    public function sort_created_at()
    {
        $serachStr = '3809544455';
        $user1 = User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'created_at' => Carbon::now()
        ]);
        $user2 = User::factory()->create([
            'new_phone' => new Phone($serachStr),
            'created_at' => Carbon::now()->addMinutes(22)
        ]);

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('CREATED_AT', 'ASC'));

        $this->assertEquals($response->json('data.users.data.0.id'), $user1->id);

        $response = $this->graphQL($this->getQueryStrOrder('CREATED_AT', 'DESC'));

        $this->assertEquals($response->json('data.users.data.0.id'), $user2->id);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_LIST])
            ->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_GET])
            ->create();
        $this->loginAsAdmin($admin);


        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            users {
                data{
                    id
                    email
                    phone
                    status
                    egrpoy
                    lang
                    name
                    createdAt
                    updatedAt
                    avatar{
                        url
                    },
                    locale {
                        name
                        locale
                    }
                }
                paginatorInfo {
                    total
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }',
        );
    }

    public function getQueryStrWithParams(array $data): string
    {
        return  sprintf('{
            users (first:%d, page:%d) {
                data{
                    id
                    email
                    status
                    name
                }
                paginatorInfo {
                    total
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }',
            $data['first'],
            $data['page']
        );
    }

    public function getQueryStrOrder(string $field, string $sort = 'ASC'): string
    {
        $field = mb_strtoupper($field);

        return  sprintf('{
            users (orderBy: [{ field: %s, order: %s }]) {
                data{
                    id
                    phone
                    status
                    name
                    createdAt
                    newPhoneEditAt
                }
               }
            }',
            $field,
            $sort
        );
    }

    public function getQueryStrNameSearch(string $name): string
    {
        return  sprintf('{
            users (userName: "%s") {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $name,
        );
    }

    public function getQueryStrPhoneSearch(string $phone): string
    {
        return  sprintf('{
            users (userPhone: "%s") {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $phone,
        );
    }

    public function getQueryStrByNewPhone(bool $hasPhone): string
    {
        $data = $hasPhone === true ? "true" : "false";
        return  sprintf('{
            users (hasNewPhone: %s) {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $data,
        );
    }

    public function getQueryStrByNewPhoneSort(bool $hasPhone, $sort): string
    {
        $data = $hasPhone === true ? "true" : "false";
        return  sprintf('{
            users (hasNewPhone: %s) {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $data,
        );
    }

    public function getQueryStrBrandSearch($brandId): string
    {
        return  sprintf('{
            users (carBrandId: "%s") {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $brandId,
        );
    }

    public function getQueryStrModelSearch($modelId): string
    {
        return  sprintf('{
            users (carModelId: "%s") {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $modelId,
        );
    }

    public function getQueryStrCarYearSearch($year): string
    {
        return  sprintf('{
            users (carYear: "%s") {
                data{
                    id
                    phone
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $year,
        );
    }
}

