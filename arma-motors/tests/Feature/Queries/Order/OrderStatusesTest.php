<?php

namespace Tests\Feature\Queries\Order;

use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class OrderStatusesTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.orderStatuses');

        $this->assertCount(6, $responseData);

        $this->assertEquals(Status::DRAFT, $responseData[0]['key']);
        $this->assertEquals(__('translation.order.status.draft'), $responseData[0]['name']);

        $this->assertEquals(Status::CREATED, $responseData[1]['key']);
        $this->assertEquals(__('translation.order.status.created'), $responseData[1]['name']);

        $this->assertEquals(Status::IN_PROCESS, $responseData[2]['key']);
        $this->assertEquals(__('translation.order.status.in_process'), $responseData[2]['name']);

        $this->assertEquals(Status::DONE, $responseData[3]['key']);
        $this->assertEquals(__('translation.order.status.done'), $responseData[3]['name']);

        $this->assertEquals(Status::CLOSE, $responseData[4]['key']);
        $this->assertEquals(__('translation.order.status.close'), $responseData[4]['name']);

        $this->assertEquals(Status::REJECT, $responseData[5]['key']);
        $this->assertEquals(__('translation.order.status.reject'), $responseData[5]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{orderStatuses {key, name}}";
    }
}
