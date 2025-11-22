<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Order\Parts;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusPaidCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class ChangeStatusPaidTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data_as_paid()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        /** @var $command OrderChangeStatusPaidCommand */
        $command = resolve(OrderChangeStatusPaidCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['status_payment'], OrderPaymentStatus::Paid->toUpperCase());
    }

    /** @test */
    public function check_prepare_data_as_not_paid()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)->create();

        /** @var $command OrderChangeStatusPaidCommand */
        $command = resolve(OrderChangeStatusPaidCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['status_payment'], OrderPaymentStatus::Not_paid->toUpperCase());
    }

    /** @test */
    public function check_prepare_data_as_refunded()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)
            ->refunded_at()->create();

        /** @var $command OrderChangeStatusPaidCommand */
        $command = resolve(OrderChangeStatusPaidCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['id'], $model->id);
        $this->assertEquals($res['status_payment'], OrderPaymentStatus::Refunded->toUpperCase());
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $command OrderChangeStatusPaidCommand */
        $command = resolve(OrderChangeStatusPaidCommand::class);

        $this->assertEquals(
            $command->getUri($command->beforeRequestForData($model)),
            str_replace('{id}', $model->id, config("requests.e_com.paths.order.parts.change_status_paid"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command OrderChangeStatusPaidCommand */
        $command = resolve(OrderChangeStatusPaidCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [OrderChangeStatusPaidCommand] you need to pass 'id' to uri"
        );

        $command->getUri($data);
    }
}
