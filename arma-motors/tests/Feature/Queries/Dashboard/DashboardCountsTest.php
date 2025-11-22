<?php

namespace Tests\Feature\Queries\Dashboard;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;

class DashboardCountsTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_not_count()
    {
        $admin = $this->adminBuilder()->setName(config('permission.roles.super_admin'))->create();
        $this->loginAsAdmin($admin);

        $this->assertTrue($admin->isSuperAdmin());

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();
        $responseData = $response->json('data.dashboardCounts');

        $this->assertArrayHasKey('usersCount', $responseData);
        $this->assertArrayHasKey('employeeCount', $responseData);
        $this->assertArrayHasKey('orderCount', $responseData);
        $this->assertArrayHasKey('orderCarCount', $responseData);

        $this->assertEquals(0, $responseData['usersCount']);
        $this->assertEquals(0, $responseData['employeeCount']);
        $this->assertEquals(0, $responseData['orderCount']);
        $this->assertEquals(0, $responseData['orderCarCount']);
    }

    /** @test */
    public function success_some_count()
    {
        $admin = $this->adminBuilder()->setName(config('permission.roles.super_admin'))->create();
        $this->loginAsAdmin($admin);
        $this->assertTrue($admin->isSuperAdmin());

        Admin::factory(3)->create();

        User::factory(3)->create();

        $this->orderBuilder()->setCount(3)->create();

        $user = User::factory()->create();
        $this->carBuilder()->setUserId($user->id)->withOrder()->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();
        $responseData = $response->json('data.dashboardCounts');

        $this->assertArrayHasKey('usersCount', $responseData);
        $this->assertArrayHasKey('employeeCount', $responseData);
        $this->assertArrayHasKey('orderCount', $responseData);
        $this->assertArrayHasKey('orderCarCount', $responseData);

        $this->assertEquals(5, $responseData['usersCount']);
        $this->assertEquals(3, $responseData['employeeCount']);
        $this->assertEquals(3, $responseData['orderCount']);
        $this->assertEquals(1, $responseData['orderCarCount']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->setName(config('permission.roles.super_admin'))->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            dashboardCounts {
                 usersCount
                 employeeCount
                 orderCount
                 orderCarCount
               }
            }'
        );
    }
}






