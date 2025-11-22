<?php

namespace Tests\Unit\Events\Listeners\Order\Parts;

use App\Enums\Orders\Parts\OrderSource;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusPaidCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderDeleteCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderUpdateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class RequestToEcomListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_send_as_change_status()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->once();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_STATUS_CHANGED);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_send_as_change_status_not_ecomm()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_STATUS_CHANGED);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_as_update()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->once();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_UPDATE);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_send_as_update_not_ecomm()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_UPDATE);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_as_delete()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->once();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_DELETE);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_send_as_delete_not_ecomm()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_DELETE);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_as_change_status_paid()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->once();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_IS_PAID);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_send_as_change_status_paid_not_ecomm()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_IS_PAID);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_as_refunded()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->once();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_REFUNDED);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_send_as_refunded_not_ecomm()
    {
        // Create a mock
        $mockChangeStatus = Mockery::mock(OrderChangeStatusCommand::class);
        $mockChangeStatusPaid = Mockery::mock(OrderChangeStatusPaidCommand::class);
        $mockUpdate = Mockery::mock(OrderUpdateCommand::class);
        $mockDelete = Mockery::mock(OrderDeleteCommand::class);

        // Define expectation
        $mockChangeStatus->shouldReceive('exec')->never();
        $mockChangeStatusPaid->shouldReceive('exec')->never();
        $mockUpdate->shouldReceive('exec')->never();
        $mockDelete->shouldReceive('exec')->never();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $event = new RequestToEcom($model, OrderPartsHistoryService::ACTION_REFUNDED);
        $listener = new RequestToEcomListener(
            $mockChangeStatus,
            $mockChangeStatusPaid,
            $mockUpdate,
            $mockDelete
        );
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
