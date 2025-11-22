<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Dealership\Dealership;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class OrderPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use OrderBuilder;
    use CarBuilder;
    use Statuses;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setAdminId($admin->id)->setCount(5)->create();
        $orderBuilder->setStatus(Status::DRAFT)->setAdminId($admin->id)->setCount(5)->create();

        $total = Order::count();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.orders');

        $this->assertEquals($total, $responseData['paginatorInfo']['total']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('uuid', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('communication', $responseData['data'][0]);
    }

    /** @test */
    public function not_see_another_order()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setAdminId($admin->id)->setCount(5)->create();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.orders');

        $this->assertEquals(5, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function see_all_as_admin()
    {
        $admin = $this->adminBuilder()
            ->asSuperAdmin()
            ->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setAdminId($admin->id)->setCount(5)->create();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setCount(5)->create();

        $this->assertTrue($admin->isSuperAdmin());

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.orders');

        $this->assertEquals(10, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function see_all_has_permission()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_LIST, Permissions::ORDER_CAN_SEE])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setAdminId($admin->id)->setCount(5)->create();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setCount(5)->create();

        $this->assertFalse($admin->isSuperAdmin());

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.orders');

        $this->assertEquals(10, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function by_status()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::DONE)->setAdminId($admin->id)->setCount(5)->create();
        $orderBuilder->setStatus(Status::DRAFT)->setAdminId($admin->id)->setCount(5)->create();

        $total = Order::where('status', Status::DONE)->count();

        $response = $this->graphQL($this->getQueryStrByStatus($this->order_status_done));

        $responseData = $response->json('data.orders');

        $this->assertEquals($total, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_user_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setUserId($user1->id)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setUserId($user2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrUserId($user1->id));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrUserId($user2->id));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_user_name()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setUserId($user1->id)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setUserId($user2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrUserName($user1->name));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrUserName($user2->name));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_responsive_name_as_admin_name()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $adminSome = Admin::factory()->create();
        $user = User::factory()->create();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setUserId($user->id)->setAdminId($adminSome->id)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrResponsiveName($adminSome->name));

        $this->assertEquals(0, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_responsive_name_as_admin_name_as_super_admin()
    {
        $admin = $this->adminBuilder()->asSuperAdmin()
            ->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $adminSome = Admin::factory()->create();
        $user = User::factory()->create();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setUserId($user->id)->setAdminId($adminSome->id)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrResponsiveName($adminSome->name));

        $this->assertEquals(1, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_responsive_name_as_admin_name_and_responsive()
    {
        $name = 'rembo';
        $adminSome = Admin::factory()->create(['name' => $name]);
        $user = User::factory()->create();

        $admin = $this->adminBuilder()->asSuperAdmin()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setUserId($user->id)->setAdminId($adminSome->id)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setAdminId($adminSome->id)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setResponsible($name)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setCount(5)->create();


        $response = $this->graphQL($this->getQueryStrResponsiveName($name));
        $this->assertEquals(3, $response->json('data.orders.paginatorInfo.total'));

    }
    /** @test */
    public function by_user_phone()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setUserId($user1->id)->setAdminId($admin->id)->setCount(2)->create();

        $orderBuilder->setUserId($user2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrUserPhone($user1->phone->getValue()));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrUserPhone($user2->phone->getValue()));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_brand_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $brand1 = Brand::find(1);
        $brand2 = Brand::find(2);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setBrandId($brand1->id)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setBrandId($brand2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrBrandId($brand1->id));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrBrandId($brand2->id));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_model_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $model1 = Model::find(10);
        $model2 = Model::find(24);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setModelId($model1->id)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setModelId($model2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrModelId($model1->id));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrModelId($model2->id));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_dealership_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $dealership1 = Dealership::find(1);
        $dealership2 = Dealership::find(2);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setDealershipId($dealership1->id)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setDealershipId($dealership2->id)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrDealershipId($dealership1->id));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrDealershipId($dealership2->id));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_id()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setCount(2)->setAdminId($admin->id)->create();
        $orderBuilder->setCount(5)->setAdminId($admin->id)->create();
        $order = $orderBuilder->asOne()->setAdminId($admin->id)->create();
        $order->refresh();

        $response = $this->graphQL($this->getQueryStrId($order->id));
        $this->assertEquals(1, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function by_service()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $serviceId1 = 1;
        $serviceId2 = 2;

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setServiceId($serviceId1)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setServiceId($serviceId2)->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrServiceId($serviceId1));
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrServiceId($serviceId2));
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function order_by_dealership()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $dealershipId1 = 1;
        $dealershipId2 = 2;

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setDealershipId($dealershipId1)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setDealershipId($dealershipId2)->setAdminId($admin->id)->setCount(5)->create();


        $response = $this->graphQL($this->getQueryStrSortDealership('ASC'));
        $this->assertEquals($dealershipId1, $response->json('data.orders.data.0.additions.dealership.id'));

        $response = $this->graphQL($this->getQueryStrSortDealership('DESC'));
        $this->assertEquals($dealershipId2, $response->json('data.orders.data.0.additions.dealership.id'));
    }

    /** @test */
    public function order_by_car()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $carBuilder = $this->carBuilder();
        $carId1 = ($carBuilder->setUserId($user->id)->create())->id;
        $carId2 = ($carBuilder->setUserId($user->id)->create())->id;

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setCarId($carId1)->setAdminId($admin->id)->setCount(2)->create();
        $orderBuilder->setCarId($carId2)->setAdminId($admin->id)->setCount(5)->create();


        $response = $this->graphQL($this->getQueryStrSortCar('ASC'));
        $this->assertEquals($carId1, $response->json('data.orders.data.0.additions.car.id'));

        $response = $this->graphQL($this->getQueryStrSortCar('DESC'));
        $this->assertEquals($carId2, $response->json('data.orders.data.0.additions.car.id'));
    }

    /** @test */
    public function order_list_for_admin_by_service()
    {
        $serviceId_1 = 1;
        $serviceId_2 = 2;

        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ORDER_LIST])
            ->setService($serviceId_1)
            ->create();
        $this->loginAsAdmin($admin);


        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setServiceId($serviceId_1)->setCount(2)->create();
        $orderBuilder->setServiceId($serviceId_2)->setCount(5)->create();


        $response = $this->graphQL($this->getQueryStr());
        $this->assertEquals(2, $response->json('data.orders.paginatorInfo.total'));

        $admin->service_id = $serviceId_2;
        $admin->save();

        $response = $this->graphQL($this->getQueryStr());
        $this->assertEquals(5, $response->json('data.orders.paginatorInfo.total'));
    }

    /** @test */
    public function not_auth()
    {
        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setCount(5)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_LIST])->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setAdminId($admin->id)->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            orders {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }'
        );
    }

    public static function getQueryStrByStatus(string $status): string
    {
        return  sprintf('{
            orders (status: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
        $status
        );
    }

    public static function getQueryStrUserId($userId): string
    {
        return  sprintf('{
            orders (userId: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $userId
        );
    }

    public static function getQueryStrUserName($name): string
    {
        return  sprintf('{
            orders (userName: "%s") {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $name
        );
    }

    public static function getQueryStrResponsiveName($name): string
    {
        return  sprintf('{
            orders (responsiveName: "%s") {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $name
        );
    }

    public static function getQueryStrUserPhone($phone): string
    {
        return  sprintf('{
            orders (userPhone: "%s") {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $phone
        );
    }

    public static function getQueryStrServiceId($serviceId): string
    {
        return  sprintf('{
            orders (serviceId: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $serviceId
        );
    }

    public static function getQueryStrBrandId($brandId): string
    {
        return  sprintf('{
            orders (brandId: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $brandId
        );
    }

    public static function getQueryStrModelId($modelId): string
    {
        return  sprintf('{
            orders (modelId: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $modelId
        );
    }

    public static function getQueryStrDealershipId($dealershipId): string
    {
        return  sprintf('{
            orders (dealershipId: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $dealershipId
        );
    }

    public static function getQueryStrId($id): string
    {
        return  sprintf('{
            orders (id: %s) {
                data{
                    id
                    uuid
                    status
                    communication
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $id
        );
    }

    public static function getQueryStrSortDealership($orderType): string
    {
        return  sprintf('{
            orders (orderByDealership: %s) {
                data{
                    id
                    uuid
                    status
                    additions
                    {
                        dealership {id}
                    }
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $orderType
        );
    }

    public static function getQueryStrSortCar($orderType): string
    {
        return  sprintf('{
            orders (orderByCar: %s) {
                data{
                    id
                    uuid
                    status
                    additions
                    {
                        car {id}
                    }
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $orderType
        );
    }
}
