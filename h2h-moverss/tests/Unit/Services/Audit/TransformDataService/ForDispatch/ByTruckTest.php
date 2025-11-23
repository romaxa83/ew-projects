<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForDispatch;

use App\Models\Audit;
use App\Models\DispatchTruck;
use App\Models\Order;
use App\Models\Order\Work;
use App\Models\Truck\Truck;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Trucks\DispatchTruckBuilder;
use Tests\Builders\Trucks\TruckBuilder;
use Tests\TestCase;

class ByTruckTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected WorkBuilder $workBuilder;
    protected TruckBuilder $truckBuilder;
    protected DispatchTruckBuilder $dispatchTruckBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->dispatchTruckBuilder = resolve(DispatchTruckBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_dispatch_truck_add_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();

        /** @var $model DispatchTruck */
        $model = $this->dispatchTruckBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'truck');
        $this->assertEquals($res[0]['details'][0]['new'],  $truck->title);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Dispatch truck (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_trucks_change_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        $truck_1 = $this->truckBuilder->create();

        /** @var $model DispatchTruck */
        $model = $this->dispatchTruckBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'truck_ids' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->old_values([
                'truck_ids' => $truck_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'truck');
        $this->assertEquals($res[0]['details'][0]['new'],  $truck_1->title);
        $this->assertEquals($res[0]['details'][0]['old'],  $truck->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Dispatch truck (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_trucks_more_change_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        $truck_1 = $this->truckBuilder->create();

        /** @var $model DispatchTruck */
        $model = $this->dispatchTruckBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'truck_ids' => $truck->id.','.$truck_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->old_values([
                'truck_ids' => $truck_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'truck');
        $this->assertEquals($res[0]['details'][0]['new'], $truck->title.', '.$truck_1->title);
        $this->assertEquals($res[0]['details'][0]['old'], $truck->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Dispatch truck (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_trucks_remove_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();

        /** @var $model DispatchTruck */
        $model = $this->dispatchTruckBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'truck_ids' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'truck');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $truck->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Dispatch truck (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }
}
