<?php

namespace Tests\Feature\Mutations\Order;

use App\Events\Order\CreateOrder;
use App\Exceptions\ErrorsCode;
use App\Listeners\Order\SendOrderToAAListeners;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Types\Communication;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class RestoreTest extends TestCase
{
    use DatabaseTransactions;
    use Statuses;
    use AdminBuilder;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ORDER_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->softDeleted()->create();

        $this->assertTrue($order->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($order->id)])
            ->assertOk();

        $responseData = $response->json('data.orderRestore');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $order->id);

        $order->refresh();

        $this->assertFalse($order->trashed());
        $this->assertTrue($order->isClose());
    }

    /** @test */
    public function success_if_reject()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ORDER_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::REJECT)->asOne()->softDeleted()->create();

        $this->assertTrue($order->trashed());
        $this->assertTrue($order->isReject());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($order->id)])
            ->assertOk();

        $responseData = $response->json('data.orderRestore');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $order->id);

        $order->refresh();

        $this->assertFalse($order->trashed());
        $this->assertTrue($order->isDraft());
    }

    /** @test */
    public function fail_not_trashed_order()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ORDER_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->create();

        $this->assertFalse($order->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($order->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.model not trashed'));
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_RESTORE])
            ->create();
        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->create();

        $this->assertFalse($order->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($order->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not auth'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->create();

        $this->assertFalse($order->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($order->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not perm'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                orderRestore(id: %s) {
                    id
                    status
                }
            }',
            $id,
        );
    }
}


