<?php

namespace Tests\Feature\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_DELETE])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->create();
        $order->refresh();

        $this->assertNull($order->deleted_at);

        $response = $this->graphQL($this->getQueryStr($order->id))
            ->assertOk();

        $responseData = $response->json('data.orderDelete');
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals(__('message.order.remove to archive'), $responseData['message']);

        $order->refresh();
        $this->assertNotNull($order->deleted_at);
    }

    /** @test */
    public function order_not_close()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_DELETE])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->asOne()->create();
        $order->refresh();

        $this->assertNull($order->deleted_at);

        $response = $this->graphQL($this->getQueryStr($order->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.must close status'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_DELETE])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ORDER_DELETE])->create();

        $response = $this->graphQL($this->getQueryStr(1));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(1));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(int $id): string
    {
        return sprintf('
            mutation {
                orderDelete(id: "%s") {
                    status
                    message
                }
            }',
            $id
        );
    }
}
