<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\CustomExtraBuilder;
use Tests\Builders\Orders\MaterialBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ByOrderMaterialTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected MaterialBuilder $materialBuilder;
    protected CustomExtraBuilder $customExtraBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->customExtraBuilder = resolve(CustomExtraBuilder::class);
        $this->materialBuilder = resolve(MaterialBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);

        parent::setUp();
    }

    /** @test */
    public function audit_material_add()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'title' => $model->title,
                'material_id' => 1,
                'type_id' => 1,
                'qty' => 1,
                'price' => 1,
                'need_packing' => 0,
                'need_unpacking' => 0,
                'packing_price' => null,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Material (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);

        $this->assertEquals($res[0]['details'][0]['field'], 'qty');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], '1');
    }

    /** @test */
    public function audit_material_add_with_packing_and_unpacking()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'title' => $model->title,
                'material_id' => 1,
                'type_id' => 1,
                'qty' => 1,
                'price' => 1,
                'need_packing' => 1,
                'need_unpacking' => 1,
                'packing_price' => null,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(3, count($res[0]['details']));

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Material (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertNull($res[0]['client']);

        $this->assertEquals($res[0]['details'][0]['field'], 'qty');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], '1');

        $this->assertEquals($res[0]['details'][1]['field'], 'need packing');
        $this->assertNull($res[0]['details'][1]['old']);
        $this->assertEquals($res[0]['details'][1]['new'], 'true');

        $this->assertEquals($res[0]['details'][2]['field'], 'need unpacking');
        $this->assertNull($res[0]['details'][2]['old']);
        $this->assertEquals($res[0]['details'][2]['new'], 'true');
    }

    /** @test */
    public function audit_material_change_price()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'qty' => 4,
                'price' => 1.4,
            ])
            ->old_values([
                'qty' => 2,
                'price' => 1,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'qty');
        $this->assertEquals($res[0]['details'][0]['new'], 4);
        $this->assertEquals($res[0]['details'][0]['old'], 2);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Material (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_material_change_need_packing()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'need_packing' => 1,
                'price' => 1.4,
                'packing_price' => 1.4,
                'unpacking_price' => 1.4,
            ])
            ->old_values([
                'need_packing' => 0,
                'price' => 1.4,
                'packing_price' => 1.4,
                'unpacking_price' => 1.4,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'need packing');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Material (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_material_change_need_unpacking()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'need_unpacking' => 1,
                'price' => 1.4,
                'packing_price' => 1.4,
                'unpacking_price' => 1.4,
            ])
            ->old_values([
                'need_unpacking' => 0,
                'price' => 1.4,
                'packing_price' => 1.4,
                'unpacking_price' => 1.4,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'need unpacking');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Material (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_material_remove()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'title' => 'Free Rolls of Tape',
                'material_id' => 1,
                'type_id' => 1,
                'qty' => 1,
                'price' => 13,
                'need_packing' => 0,
                'need_unpacking' => 0,
                'packing_price' => null,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $model->delete();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'qty');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], '1');

        $this->assertEquals($res[0]['details'][1]['field'], 'price');
        $this->assertNull($res[0]['details'][1]['new']);
        $this->assertEquals($res[0]['details'][1]['old'], '13');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Material (Free Rolls of Tape)");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function not_audit_same_price_fields()
    {
        /** @var $model Order\Material */
        $model = $this->materialBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'price' => 12,
            ])
            ->old_values([
                'price' => "12.00",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEmpty($res);
    }

    /** @test */
    public function audit_custom_material_add()
    {
        /** @var $model Order\CustomExtra */
        $model = $this->customExtraBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'title' => $model->title,
                'price' => 3,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'price');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Material custom (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_custom_material_change_price()
    {
        /** @var $model Order\CustomExtra */
        $model = $this->customExtraBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'price' => 3,
            ])
            ->old_values([
                'price' => 1.0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'price');
        $this->assertEquals($res[0]['details'][0]['new'], 3);
        $this->assertEquals($res[0]['details'][0]['old'], 1);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Material custom (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_custom_material_remove()
    {
        /** @var $model Order\CustomExtra */
        $model = $this->customExtraBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'title' => $model->title,
                'price' => 3,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'price');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Material custom (".$model->title.")");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }


}
