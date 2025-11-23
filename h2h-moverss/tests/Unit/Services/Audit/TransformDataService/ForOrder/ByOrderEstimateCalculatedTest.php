<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\EstimateBuilder;
use Tests\Builders\Orders\EstimateCalculatedBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ByOrderEstimateCalculatedTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected EstimateBuilder $estimateBuilder;
    protected EstimateCalculatedBuilder $estimateCalculatedBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->estimateBuilder = resolve(EstimateBuilder::class);
        $this->estimateCalculatedBuilder = resolve(EstimateCalculatedBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_estimate_calculated_add()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $calculated = $this->estimateCalculatedBuilder
            ->order($model)
            ->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($calculated)
            ->new_values([
                'id' => $calculated->id,
                'order_id' => $model->id,
                'estimate_type' => 'local',
                'title' => 'left2pay',
                'value' => '$472',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(3, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'estimate type');
        $this->assertEquals($res[0]['details'][0]['new'], 'local');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['details'][1]['field'], 'title');
        $this->assertEquals($res[0]['details'][1]['new'], 'left2pay');
        $this->assertNull($res[0]['details'][1]['old']);

        $this->assertEquals($res[0]['details'][2]['field'], 'value');
        $this->assertEquals($res[0]['details'][2]['new'], '$472');
        $this->assertNull($res[0]['details'][2]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (calculated)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_estimate_calculated_change_value()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $calculated = $this->estimateCalculatedBuilder
            ->order($model)
            ->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($calculated)
            ->new_values([
                'value' => '$187 - $173',
            ])
            ->old_values([
                'value' => '$175',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'value');
        $this->assertEquals($res[0]['details'][0]['new'], '$187 - $173');
        $this->assertEquals($res[0]['details'][0]['old'], '$175');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Estimate (calculated)');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}

