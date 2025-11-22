<?php

namespace Tests\Feature\Queries\Admin;

use App\Models\Catalogs\Service\Service;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class AuthAdminTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function auth_success()
    {
        $service = Service::find(1);

        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ROLE_LIST, Permissions::ROLE_GET])
            ->setService($service->id)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authAdmin');
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals($responseData['email'], $admin->email);
        $this->assertEquals($responseData['name'], $admin->name);
        $this->assertEquals($responseData['id'], $admin->id);
        $this->assertEquals($responseData['status'], $this->admin_status_active);
        // locale
        $this->assertArrayHasKey('locale', $responseData);
        $this->assertArrayHasKey('name', $responseData['locale']);
        $this->assertArrayHasKey('slug', $responseData['locale']);
        $this->assertArrayHasKey('locale', $responseData['locale']);
        // role/perm
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('name', $responseData['role']);
        $this->assertArrayHasKey('current', $responseData['role']);
        $this->assertArrayHasKey('name', $responseData['role']['current']);
        $this->assertArrayHasKey('lang', $responseData['role']['current']);
        $this->assertArrayHasKey('translations', $responseData['role']);
        $this->assertArrayHasKey('permissions', $responseData['role']);
        $this->assertCount(2, $responseData['role']['permissions']);
        $this->assertCount(2, $responseData['role']['translations']);
        $this->assertArrayHasKey('name', $responseData['role']['permissions'][0]);
        $this->assertArrayHasKey('name', $responseData['role']['translations'][0]);
        $this->assertEquals($responseData['role']['name'], $admin->role->name);
        $this->assertEquals($responseData['role']['current']['lang'], $admin->lang);
        $this->assertEquals($responseData['role']['current']['name'], $admin->role->current->name);
        // avatar
        $this->assertArrayHasKey('avatar', $responseData);
        $this->assertNull($responseData['avatar']);
        // dealership
        $this->assertArrayHasKey('dealership', $responseData);
        $this->assertNull($responseData['dealership']);
        // orders count
        $this->assertArrayHasKey('ordersCount', $responseData);
        $this->assertEquals(0, $responseData['ordersCount']);
        $this->assertArrayHasKey('ordersCloseCount', $responseData);
        $this->assertEquals(0, $responseData['ordersCloseCount']);
        $this->assertArrayHasKey('ordersWithArchiveCount', $responseData);
        $this->assertEquals(0, $responseData['ordersWithArchiveCount']);
        $this->assertArrayHasKey('ordersCloseWithArchiveCount', $responseData);
        $this->assertEquals(0, $responseData['ordersCloseWithArchiveCount']);
        // service
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);
        $this->assertEquals($responseData['service']['id'], $service->id);

        //@todo test добавить проверки для разрешений
    }

    /** @test */
    public function auth_wrong()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ROLE_LIST, Permissions::ROLE_GET])
            ->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            authAdmin {
                id
                email
                name
                status
                locale {
                    name
                    slug
                    locale
                }
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
                }
                avatar {
                    id
                    url
                    sizes
                }
                dealership {
                    id
                }
                ordersCount
                ordersCloseCount
                ordersWithArchiveCount
                ordersCloseWithArchiveCount
                service {
                    id
                }
               }
            }'
        );
    }
}

