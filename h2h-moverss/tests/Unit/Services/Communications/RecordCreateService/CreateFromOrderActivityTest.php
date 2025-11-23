<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Enums\Orders\ActivityType;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Services\Communications\RecordCreateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\ActivityBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class CreateFromOrderActivityTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected OrderBuilder $orderBuilder;
    protected ActivityBuilder $activityBuilder;
    protected DivisionBuilder $divisionBuilder;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        $now = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder
            ->client($client)
            ->division($division)
            ->create();

        /** @var $model Order */
        $model = $this->activityBuilder
            ->type(ActivityType::Status)
            ->order($order)
            ->updated_at($now->subHour())
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->order_id, $order->id);
        $this->assertEquals($rec->client_id, $client->id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertNull($rec->channel_contact);
        $this->assertEquals($rec->sort_at, $model->updated_at);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(Order\Activity::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }
}
