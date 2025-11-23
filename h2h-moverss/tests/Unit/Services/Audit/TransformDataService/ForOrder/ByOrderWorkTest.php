<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ByOrderWorkTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected WorkBuilder $workBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);

        parent::setUp();
    }

    /** @test */
    public function audit_order_work_change_truck()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        /** @var $work Order\Work */
        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'trucks' => 1,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'trucks' => 3,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'trucks qty');
        $this->assertEquals($res[0]['details'][0]['new'], 1);
        $this->assertEquals($res[0]['details'][0]['old'], 3);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_delete_truck()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'trucks' => 0,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'trucks' => 3,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'trucks qty');
        $this->assertEquals($res[0]['details'][0]['new'], 0);
        $this->assertEquals($res[0]['details'][0]['old'], 3);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_employees()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'employees' => 1,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'employees' => 3,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'employees qty');
        $this->assertEquals($res[0]['details'][0]['new'], 1);
        $this->assertEquals($res[0]['details'][0]['old'], 3);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_employees_and_trucks()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'trucks' => 2,
                'employees' => 1,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'trucks' => 4,
                'employees' => 3,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'trucks qty');
        $this->assertEquals($res[0]['details'][0]['new'], 2);
        $this->assertEquals($res[0]['details'][0]['old'], 4);

        $this->assertEquals($res[0]['details'][1]['field'], 'employees qty');
        $this->assertEquals($res[0]['details'][1]['new'], 1);
        $this->assertEquals($res[0]['details'][1]['old'], 3);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_duration()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'duration' => 1,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'duration' => 3,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'duration');
        $this->assertEquals($res[0]['details'][0]['new'], 1);
        $this->assertEquals($res[0]['details'][0]['old'], 3);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_add_notes()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'notes' => 'text',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'notes' => null,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'notes');
        $this->assertEquals($res[0]['details'][0]['new'], 'text');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_notes()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'notes' => 'text up',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'notes' => 'text',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'notes');
        $this->assertEquals($res[0]['details'][0]['new'], 'text up');
        $this->assertEquals($res[0]['details'][0]['old'], 'text');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_delete_notes()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'notes' => null,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'notes' => 'text',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'notes');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], 'text');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_add_start_time()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_time' => '14:22:08',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_time' => null,
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], '14:22:08');

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_start_time()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_time' => '14:22:08',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_time' => '13:22:08',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time');
        $this->assertEquals($res[0]['details'][0]['new'], '14:22:08');
        $this->assertEquals($res[0]['details'][0]['old'], '13:22:08');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_delete_start_time()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_time' => null,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_time' => '14:22:08',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time');
        $this->assertEquals($res[0]['details'][0]['old'], '14:22:08');
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_add_start_time_and_start_time_to()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->old_values([
                'start_time' => null,
                'start_time_to' => null,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->new_values([
                'start_time' => '14:22:08',
                'start_time_to' => '15:22:08',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time');
        $this->assertEquals($res[0]['details'][0]['new'], '14:22:08');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['details'][1]['field'], 'start time to');
        $this->assertEquals($res[0]['details'][1]['new'], '15:22:08');
        $this->assertNull($res[0]['details'][1]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_start_time_to()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_time' => null,
                'start_time_to' => '11:22:08',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_time_to' => '15:22:08',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time to');
        $this->assertEquals($res[0]['details'][0]['new'], '11:22:08');
        $this->assertEquals($res[0]['details'][0]['old'], '15:22:08');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_start_date()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_date' => '2024-09-23',
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_date' => '2024-09-22',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start date');
        $this->assertEquals($res[0]['details'][0]['new'], '2024-09-23');
        $this->assertEquals($res[0]['details'][0]['old'], '2024-09-22');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_delete_start_time_and_start_time_to()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'start_time' => null,
                'start_time_to' => null,
                'notes_created_at' => '2024-09-23 14:22:08',
            ])
            ->old_values([
                'start_time' => '14:22:08',
                'start_time_to' => '15:22:08',
                'notes_created_at' => '2024-09-23 14:22:08'
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'start time');
        $this->assertnull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], '14:22:08');

        $this->assertEquals($res[0]['details'][1]['field'], 'start time to');
        $this->assertnull($res[0]['details'][1]['new']);
        $this->assertEquals($res[0]['details'][1]['old'], '15:22:08');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_work_type()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'workTypes' => [],
                'custom_work_types' => ['type 1']
            ])
            ->old_values([
                'workTypes' => [
                    [
                        'id' => 1,
                        'sort' => 1,
                        'title' => 'type 1',
                    ]
                ],
                'custom_work_types' => ['type 2', 'type 3']
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'work types');
        $this->assertEquals($res[0]['details'][0]['new'], 'type 1');
        $this->assertEquals($res[0]['details'][0]['old'], 'type 2, type 3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_work_change_dispatch_to_true()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $work = $this->workBuilder->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($work)
            ->new_values([
                'in_dispatch' => 1,
            ])
            ->old_values([
                'in_dispatch' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'in dispatch');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Service');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
