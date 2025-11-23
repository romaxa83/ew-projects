<?php

namespace Tests\Feature\Orders\Statuses;

use App\Enums\Communications\Type;
use App\Enums\Orders\ActivityType;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class SetTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected StatusBuilder $statusBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_set()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $oldStatus = $this->statusBuilder->create();
        $newStatus = $this->statusBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->status($oldStatus)->create();

        $data = [
            'order_id' => $order->id,
            'old_status' => $oldStatus->id,
            'is_roll_back' => 0,
            'status_id' => $newStatus->id,
        ];

        $this->assertEmpty($order->activities);
        $this->assertNull(CommunicationRecord::first());

        $this->post(route('orders.record.setStatus', [
            'id' => $order->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Status changed",
                'prev_status' => $oldStatus->id,
            ])
        ;

        $order->refresh();
        /** @var $activity Order\Notes */
        $activity = $order->activities->first();

        $this->assertEquals($activity->user_id, $user->id);
        $this->assertEquals($activity->type, ActivityType::Status->value);
        $this->assertEquals($activity->miscs['from'], $oldStatus->id);
        $this->assertEquals($activity->miscs['to'], $newStatus->id);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $activity->id);
        $this->assertEquals($rec->entity_type, Order\Activity::MORPH_NAME);
        $this->assertEquals($rec->client_id, $order->client_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $activity->created_at);
    }
}
