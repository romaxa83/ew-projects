<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order\Inventory;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ByOrderInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);

        parent::setUp();
    }

    /** @test */
    public function audit_inventory_add()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'is_section' => 1,
                'price' => null,
                'qty' => null,
                'weight' => null,
                'volume' => null,
                'title' => 'kitchen',
                'sort' => 1,
                'item_id' => 22,
                'section_id' => 0,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'title');
        $this->assertEquals($res[0]['details'][0]['new'], 'kitchen');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_remove()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'is_section' => 1,
                'price' => 10,
                'qty' => 1,
                'weight' => 10,
                'volume' => 10,
                'title' => 'kitchen',
                'sort' => 1,
                'item_id' => 22,
                'section_id' => 0,
                'order_id' => $model->order_id,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'title');
        $this->assertEquals($res[0]['details'][0]['old'], 'kitchen');
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_change_title()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'title' => 'kitchen',
            ])
            ->old_values([
                'title' => 'bedroom',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'title');
        $this->assertEquals($res[0]['details'][0]['new'], 'kitchen');
        $this->assertEquals($res[0]['details'][0]['old'], 'bedroom');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_add_item()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'title' => $model->title,
                'weight' => 75,
                'volume' => 10,
                'item_id' => 22,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(3, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'title');
        $this->assertEquals($res[0]['details'][0]['new'], $model->title);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['details'][1]['field'], 'weight');
        $this->assertEquals($res[0]['details'][1]['new'], '75');
        $this->assertNull($res[0]['details'][1]['old']);

        $this->assertEquals($res[0]['details'][2]['field'], 'volume');
        $this->assertEquals($res[0]['details'][2]['new'], '10');
        $this->assertNull($res[0]['details'][2]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_item_change_price()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'price' => 11,
            ])
            ->old_values([
                'price' => "10.0",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'price');
        $this->assertEquals($res[0]['details'][0]['new'], '11');
        $this->assertEquals($res[0]['details'][0]['old'], '10.0');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_item_change_weight()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'weight' => 74,
            ])
            ->old_values([
                'weight' => "75.00",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'weight');
        $this->assertEquals($res[0]['details'][0]['new'], '74');
        $this->assertEquals($res[0]['details'][0]['old'], '75.00');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_item_change_volume()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'volume' => 74,
            ])
            ->old_values([
                'volume' => "75.00",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'volume');
        $this->assertEquals($res[0]['details'][0]['new'], '74');
        $this->assertEquals($res[0]['details'][0]['old'], '75.00');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_inventory_item_change_qty()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'qty' => 7,
            ])
            ->old_values([
                'qty' => 5,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'qty');
        $this->assertEquals($res[0]['details'][0]['new'], '7');
        $this->assertEquals($res[0]['details'][0]['old'], '5');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Inventory ({$model->title})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
