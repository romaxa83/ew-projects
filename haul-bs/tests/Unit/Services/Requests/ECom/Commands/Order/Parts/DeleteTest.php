<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Order\Parts;

use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderDeleteCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $command OrderDeleteCommand */
        $command = resolve(OrderDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command OrderDeleteCommand */
        $command = resolve(OrderDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [OrderDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $command OrderDeleteCommand */
        $command = resolve(OrderDeleteCommand::class);
        $this->assertEquals(
            $command->getUri(['id' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.order.parts.delete"))
        );
    }
}
