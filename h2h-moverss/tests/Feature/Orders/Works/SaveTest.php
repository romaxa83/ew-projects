<?php

namespace Tests\Feature\Orders\Works;

use App\Models\Audit;
use App\Models\Division;
use App\Models\Order;
use App\Models\Order\Work;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Works\WorkTypeBuilder;
use Tests\TestCase;

class SaveTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected WorkBuilder $workBuilder;
    protected WorkTypeBuilder $workTypeBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->workTypeBuilder = resolve(WorkTypeBuilder::class);

        $this->data = [
            'employees' => 1,
            'trucks' => 1,
            'start_date' => '2019-01-01',
            'start_time' => null,
            'start_time_to' => null,
            'notes' => null,
        ];

        parent::setUp();
    }

    /** @test */
    public function success_add_work_type_and_check_audit(): void
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();

        $workType_1 = $this->workTypeBuilder->create();

        $data = $this->data;
        $data['id'] = $work->id;
        $data['order_id'] = $order->id;
        $data['work_types_checked'] = [
            $workType_1->id,
        ];

        $this->assertEmpty($work->workTypes);

        $this->post(route('orders.works.save'), $data);

        $work->refresh();

        $type = $work->workTypes[0];

        $this->assertEquals($workType_1->id, $type->id);

        $audit = Audit::where('auditable_type', Work::MORPH_NAME)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals(0, count($audit->old_values['custom_work_types']));
        $this->assertEquals($audit->new_values['custom_work_types'], [$workType_1->title]);
    }

    /** @test */
    public function success_change_work_type_and_check_audit(): void
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $workType_1 = $this->workTypeBuilder->create();
        $workType_2 = $this->workTypeBuilder->create();
        $workType_3 = $this->workTypeBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $work Work */
        $work = $this->workBuilder
            ->types($workType_1)
            ->order($order)->create();

        $data = $this->data;
        $data['id'] = $work->id;
        $data['order_id'] = $order->id;
        $data['work_types_checked'] = [
            $workType_2->id,
            $workType_3->id,
        ];

        $this->assertCount(1, $work->workTypes);

        $this->post(route('orders.works.save'), $data);

        $work->refresh();

        $this->assertCount(2, $work->workTypes);

        $audit = Audit::where('auditable_type', Work::MORPH_NAME)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals($audit->old_values['custom_work_types'], [$workType_1->title]);
        $this->assertEquals($audit->new_values['custom_work_types'], [$workType_2->title, $workType_3->title]);
    }

    /** @test */
    public function success_change_work_type_without_work_id_and_check_audit(): void
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $workType_1 = $this->workTypeBuilder->create();
        $workType_2 = $this->workTypeBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data;
        $data['order_id'] = $order->id;
        $data['work_types_checked'] = [
            $workType_1->id,
            $workType_2->id,
        ];

        $res = $this->post(route('orders.works.save'), $data)
            ->json('records.0')
        ;

        $audit = Audit::query()
            ->where('auditable_type', Work::MORPH_NAME)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals(count($audit->old_values['custom_work_types']), 0);
        $this->assertEquals($audit->new_values['custom_work_types'], [$workType_1->title, $workType_2->title]);
    }
}

