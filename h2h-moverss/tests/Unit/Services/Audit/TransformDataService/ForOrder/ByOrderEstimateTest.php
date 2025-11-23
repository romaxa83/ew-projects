<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order;
use App\Models\Order\Estimate;
use App\Models\Order\Estimate\Interstate;
use App\Models\Order\Estimate\Local;
use App\Services\Audit\TransformDataService;
use Database\Factories\Orders\EstimateInterstateFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\EstimateBuilder;
use Tests\Builders\Orders\MoveSizeBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\SourceBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ByOrderEstimateTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected EstimateBuilder $estimateBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->estimateBuilder = resolve(EstimateBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_estimate_change_move_type()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'type' => 'local',
                'fee_type' => 'percent',
                'travel_fee' => '0.50',
            ])
            ->old_values([
                'type' => 'interstate',
                'fee_type' => 'percent',
                'travel_fee' => '0.60',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'move type');
        $this->assertEquals($res[0]['details'][0]['new'], 'local');
        $this->assertEquals($res[0]['details'][0]['old'], 'interstate');

        $this->assertEquals($res[0]['details'][1]['field'], 'travel fee');
        $this->assertEquals($res[0]['details'][1]['new'], '0.50');
        $this->assertEquals($res[0]['details'][1]['old'], '0.60');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_add_discount_value()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'discount_value' => 5,
            ])
            ->old_values([
                'discount_value' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'discount');
        $this->assertEquals($res[0]['details'][0]['new'], '5');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Estimate');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_change_discount_value()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'discount_value' => 5,
            ])
            ->old_values([
                'discount_value' => 4,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'discount');
        $this->assertEquals($res[0]['details'][0]['new'], '5');
        $this->assertEquals($res[0]['details'][0]['old'], '4');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_change_calculated_moving_distance_is_auto()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'calculated_moving_distance_is_auto' => 1,
            ])
            ->old_values([
                'calculated_moving_distance_is_auto' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'calculated moving distance is auto');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_change_calculated_moving_distance()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'calculated_moving_distance' => 1.6,
            ])
            ->old_values([
                'calculated_moving_distance' => 10,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'calculated moving distance');
        $this->assertEquals($res[0]['details'][0]['new'], '1.6');
        $this->assertEquals($res[0]['details'][0]['old'], '10');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_estimate_rate()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'estimate_rate' => 'expedited',
            ])
            ->old_values([
                'estimate_rate' => 'consolidated',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'estimate rate');
        $this->assertEquals($res[0]['details'][0]['new'], 'expedited');
        $this->assertEquals($res[0]['details'][0]['old'], 'consolidated');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_packing_and_estimate_rate()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'packing' => '3.00',
                'estimate_rate' => 'expedited',
            ])
            ->old_values([
                'packing' => '0.00',
                'estimate_rate' => 'consolidated',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'packing');
        $this->assertEquals($res[0]['details'][0]['new'], '3.00');
        $this->assertEquals($res[0]['details'][0]['old'], '0.00');

        $this->assertEquals($res[0]['details'][1]['field'], 'estimate rate');
        $this->assertEquals($res[0]['details'][1]['new'], 'expedited');
        $this->assertEquals($res[0]['details'][1]['old'], 'consolidated');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_unpacking()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'unpacking' => '3.00',
            ])
            ->old_values([
                'unpacking' => '0.00',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'unpacking');
        $this->assertEquals($res[0]['details'][0]['new'], '3.00');
        $this->assertEquals($res[0]['details'][0]['old'], '0.00');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_shuttle_pickup()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'shuttle_pickup' => 1,
            ])
            ->old_values([
                'shuttle_pickup' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'shuttle pickup');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_shuttle_delivery()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'shuttle_delivery' => 1,
            ])
            ->old_values([
                'shuttle_delivery' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'shuttle delivery');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_delivery_days()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'delivery_days' => "5",
            ])
            ->old_values([
                'delivery_days' => "1-10 business days",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'delivery days');
        $this->assertEquals($res[0]['details'][0]['new'], '5');
        $this->assertEquals($res[0]['details'][0]['old'], '1-10 business days');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_rate()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'rate' => "5",
            ])
            ->old_values([
                'rate' => "0.00",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'rate');
        $this->assertEquals($res[0]['details'][0]['new'], '5');
        $this->assertEquals($res[0]['details'][0]['old'], '0.00');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_interstate_change_is_auto()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Interstate::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'is_auto' => 1,
            ])
            ->old_values([
                'is_auto' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'is auto');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (interstate)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_local_change_hours_max()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate\Local::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'hours_max' => 1.0,
            ])
            ->old_values([
                'hours_max' => 3,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'hours max');
        $this->assertEquals($res[0]['details'][0]['new'], '1.0');
        $this->assertEquals($res[0]['details'][0]['old'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (local)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_local_change_is_auto_and_hour_max()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate\Local::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'is_auto' => 1,
                'hours_max' => 1.0,
            ])
            ->old_values([
                'is_auto' => 0,
                'hours_max' => 3,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'is auto');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['details'][1]['field'], 'hours max');
        $this->assertEquals($res[0]['details'][1]['new'], '1.0');
        $this->assertEquals($res[0]['details'][1]['old'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (local)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_local_change_rate()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $estimate = Estimate\Local::factory()->create();
        $estimate->order_id = $model->id;
        $estimate->save();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($estimate)
            ->new_values([
                'rate' => "1.9",
            ])
            ->old_values([
                'rate' => "2",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'rate');
        $this->assertEquals($res[0]['details'][0]['new'], '1.9');
        $this->assertEquals($res[0]['details'][0]['old'], '2');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (local)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}

