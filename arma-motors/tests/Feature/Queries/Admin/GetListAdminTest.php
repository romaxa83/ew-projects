<?php

namespace Tests\Feature\Queries\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;

class GetListAdminTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        Admin::factory()->count(20)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.admins');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertCount(10, $responseData['data']);

        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('email', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]);
        $this->assertArrayHasKey('createdAt', $responseData['data'][0]);
        $this->assertArrayHasKey('lastLoginAt', $responseData['data'][0]);
        $this->assertArrayHasKey('role', $responseData['data'][0]);

        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['paginatorInfo']['count'], 10);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], 1);
        $this->assertTrue($responseData['paginatorInfo']['hasMorePages']);
        $this->assertEquals($responseData['paginatorInfo']['lastPage'], 3);
    }

    /** @test */
    public function get_success_with_params()
    {
        Admin::factory()->count(20)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($admin);

        $params = [
            'first' => 3,
            'page' => 3
        ];

        $response = $this->graphQL($this->getQueryStrWithParams($params))
            ->assertOk();

        $responseData = $response->json('data.admins');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertCount($params['first'], $responseData['data']);

        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertEquals($responseData['paginatorInfo']['count'], $params['first']);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], $params['page']);
        $this->assertTrue($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function get_success_order_by_id()
    {
        Admin::factory()->count(5)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('id'));

        $responseData = $response->json('data.admins');
        $firstElement = current($responseData['data']);

        $response = $this->graphQL($this->getQueryStrOrder('id', 'DESC'));

        $responseData = $response->json('data.admins');
        $newFirstElement = current($responseData['data']);
        $newLastElement = last($responseData['data']);

        $this->assertNotEquals($firstElement['id'], $newFirstElement['id']);
        $this->assertEquals($firstElement['id'], $newLastElement['id']);
    }

    /** @test */
    public function get_success_order_by_email()
    {
        Admin::factory()->count(5)->create();

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOrder('email'));

        $responseData = $response->json('data.admins');
        $firstElement = current($responseData['data']);

        $response = $this->graphQL($this->getQueryStrOrder('email', 'DESC'));

        $responseData = $response->json('data.admins');
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

        $responseData = $response->json('data.admins');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertEmpty($responseData['data']);

        $this->assertEquals($responseData['paginatorInfo']['count'], 0);
        $this->assertEquals($responseData['paginatorInfo']['currentPage'], 1);
        $this->assertEquals($responseData['paginatorInfo']['lastPage'], 1);
        $this->assertFalse($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function search_by_username()
    {
        $serachStr = 'rembo';
        Admin::factory()->count(5)->create();
        Admin::factory()->create(['name' => $serachStr]);
        Admin::factory()->create(['name' => $serachStr . ' stal']);

        $total = Admin::query()->where('name','like', '%' . $serachStr . '%')->count();

        $this->assertNotEquals(0, $total);

        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrNameSearch($serachStr))->assertOk();

        $this->assertEquals($response->json('data.admins.paginatorInfo.total'), $total);
    }

    /** @test */
    public function sort_by_role()
    {
        $this->adminBuilder()
            ->setEmail("admin@gmail.com")
            ->setRoleName('Админ')
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();

        $this->adminBuilder()
            ->setEmail("god@gmail.com")
            ->setRoleName('Бог')
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();

        $a = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($a);

        $response = $this->graphQL($this->getQuerySortRole("ASC"))->assertOk();

        $firstId = $response->json('data.admins.data.0.id');

        $response = $this->graphQL($this->getQuerySortRole("DESC"))->assertOk();

        $this->assertNotEquals($firstId, $response->json('data.admins.data.0.id'));
    }

    /** @test */
    public function sort_by_count_order()
    {
        $admin = $this->adminBuilder()
            ->setEmail("admin@gmail.com")
            ->setRoleName('Админ')
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();
        $this->orderBuilder()->setAdminId($admin->id)->create();
        $this->orderBuilder()->setAdminId($admin->id)->create();

        $a = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($a);

        $response = $this->graphQL($this->getQuerySortCountOrder("DESC"))->assertOk();

        $this->assertEquals(2, count($response->json('data.admins.data')));
        $this->assertEquals(2, $response->json('data.admins.data.0.ordersCount'));
        $this->assertEquals(0, $response->json('data.admins.data.1.ordersCount'));

        $response = $this->graphQL($this->getQuerySortCountOrder("ASC"))->assertOk();

        $this->assertEquals(2, count($response->json('data.admins.data')));
        $this->assertEquals(0, $response->json('data.admins.data.0.ordersCount'));
        $this->assertEquals(2, $response->json('data.admins.data.1.ordersCount'));
    }

    /** @test */
    public function sort_by_count_close_order()
    {
        $admin = $this->adminBuilder()
            ->setEmail("admin@gmail.com")
            ->setRoleName('Админ')
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();
        $this->orderBuilder()->setStatus(Status::CLOSE)->setAdminId($admin->id)->create();
        $this->orderBuilder()->setStatus(Status::CLOSE)->setAdminId($admin->id)->create();
        $this->orderBuilder()->setAdminId($admin->id)->create();

        $a = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_LIST])->create();
        $this->loginAsAdmin($a);

        $response = $this->graphQL($this->getQuerySortCountCloseOrder("DESC"))->assertOk();

        $this->assertEquals(2, count($response->json('data.admins.data')));
        $this->assertEquals(2, $response->json('data.admins.data.0.ordersCloseCount'));
        $this->assertEquals(0, $response->json('data.admins.data.1.ordersCloseCount'));

        $response = $this->graphQL($this->getQuerySortCountCloseOrder("ASC"))->assertOk();

        $this->assertEquals(2, count($response->json('data.admins.data')));
        $this->assertEquals(0, $response->json('data.admins.data.0.ordersCloseCount'));
        $this->assertEquals(2, $response->json('data.admins.data.1.ordersCloseCount'));
    }

    /** @test */
    public function get_not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function get_not_perm()
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
            admins {
                data{
                    id
                    email
                    status
                    name
                    createdAt
                    lastLoginAt
                    role {
                        name
                        current {
                            lang
                            name
                        }
                        translations {
                            name
                        }
                        permissions {
                            name
                        }
                    },
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

    public function getQueryStrWithParams(array $data): string
    {
        return  sprintf('{
            admins (first:%d, page:%d) {
                data{
                    id
                    email
                    status
                    name
                }
                paginatorInfo {
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
            admins (orderBy: [{ field: %s, order: %s }]) {
                data{
                    id
                    email
                    status
                    name
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
            admins (userName: "%s") {
                data{
                    id
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

    public function getQuerySortRole(string $type): string
    {
        return  sprintf('{
            admins (orderByRole: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $type,
        );
    }

    public function getQuerySortCountOrder(string $type): string
    {
        return  sprintf('{
            admins (orderByCountOrders: "%s") {
                data{
                    id
                    ordersCount
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $type,
        );
    }

    public function getQuerySortCountCloseOrder(string $type): string
    {
        return  sprintf('{
            admins (orderByCountCloseOrders: "%s") {
                data{
                    id
                    ordersCloseCount
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $type,
        );
    }
}
