<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Models\Audit;
use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\NoteBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\SourceBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ByOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected NoteBuilder $orderNoteBuilder;
    protected SourceBuilder $sourceBuilder;
    protected StatusBuilder $statusBuilder;
    protected DivisionBuilder $divisionBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderNoteBuilder = resolve(NoteBuilder::class);
        $this->sourceBuilder = resolve(SourceBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_order_add_manager()
    {
        $newUser = $this->userBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'user_id' => $newUser->id,
                'updated_by' => 1,
            ])
            ->old_values([
                'user_id' => null,
                'updated_by' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'user');
        $this->assertEquals($res[0]['details'][0]['new'], $newUser->name);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_manager()
    {
        $newUser = $this->userBuilder->create();
        $oldUser = $this->userBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'user_id' => $newUser->id,
            ])
            ->old_values([
                'user_id' => $oldUser->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'user');
        $this->assertEquals($res[0]['details'][0]['new'], $newUser->name);
        $this->assertEquals($res[0]['details'][0]['old'], $oldUser->name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_move_size()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'move_size_id' => MoveSizeTypeEnum::Storage(),
                'updated_by' => null,
            ])
            ->old_values([
                'move_size_id' => null,
                'updated_by' => 1,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'move size');
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Storage())
        );
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_move_size()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'move_size_id' => MoveSizeTypeEnum::Storage(),
                'updated_by' => null,
            ])
            ->old_values([
                'move_size_id' => MoveSizeTypeEnum::Studio(),
                'updated_by' => 1,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'move size');
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Storage())
        );
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Studio())
        );

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_building_type()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'type' => 'house',
            ])
            ->old_values([
                'type' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'type');
        $this->assertEquals($res[0]['details'][0]['new'], 'house');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_building_type()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'type' => 'house',
            ])
            ->old_values([
                'type' => 'business',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'type');
        $this->assertEquals($res[0]['details'][0]['new'], 'house');
        $this->assertEquals($res[0]['details'][0]['old'], 'business');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_source()
    {
        $newModel = $this->sourceBuilder->create();
        $oldModel = $this->sourceBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'source_id' => $newModel->id,
            ])
            ->old_values([
                'source_id' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'source');
        $this->assertEquals($res[0]['details'][0]['new'], $newModel->title);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_source()
    {
        $newModel = $this->sourceBuilder->create();
        $oldModel = $this->sourceBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'source_id' => $newModel->id,
            ])
            ->old_values([
                'source_id' => $oldModel->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'source');
        $this->assertEquals($res[0]['details'][0]['new'], $newModel->title);
        $this->assertEquals($res[0]['details'][0]['old'], $oldModel->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_field_tags()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [],
                'custom_tags' => [],
            ])
            ->new_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "tag 1",
                    ],
                    [
                        'id' => 2,
                        'color' => "red",
                        'title' => "tag 2",
                    ]
                ],
                'custom_tags' => ["tag 1", "tag 2"]
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertEquals($res[0]['details'][0]['new'],"tag 1, tag 2");
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_field_tags()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [],
                'custom_tags' => ["tag 1", "tag 3"]
            ])
            ->new_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "tag 1",
                    ],
                    [
                        'id' => 2,
                        'color' => "red",
                        'title' => "tag 2",
                    ]
                ],
                'custom_tags' => ["tag 1", "tag 2"]
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertEquals($res[0]['details'][0]['new'],"tag 1, tag 2");
        $this->assertEquals($res[0]['details'][0]['old'],"tag 1, tag 3");

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_delete_field_tags()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "ee",
                    ],
                ],
                'custom_tags' => ['tag 1']
            ])
            ->new_values([
                'tags' => [],
                'custom_tags' => []
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'],"tag 1");

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_field_status()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $status_1 = $this->statusBuilder->create();
        $status_2 = $this->statusBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'status_id' => $status_1->id,
            ])
            ->new_values([
                'status_id' => $status_2->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'status');
        $this->assertEquals($res[0]['details'][0]['new'], $status_2->title);
        $this->assertEquals($res[0]['details'][0]['old'], $status_1->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_field_sizing_volume()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'sizing_volume' => null,
            ])
            ->new_values([
                'sizing_volume' => '3',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing volume');
        $this->assertEquals($res[0]['details'][0]['new'], '3');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_field_sizing_volume()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'sizing_volume' => '5',
            ])
            ->new_values([
                'sizing_volume' => '3',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing volume');
        $this->assertEquals($res[0]['details'][0]['new'], '3');
        $this->assertEquals($res[0]['details'][0]['old'], '5');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_remove_field_sizing_volume()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->new_values([
                'sizing_volume' => null,
            ])
            ->old_values([
                'sizing_volume' => '3',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing volume');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_field_sizing_weight()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'sizing_weight' => null,
            ])
            ->new_values([
                'sizing_weight' => '3',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing weight');
        $this->assertEquals($res[0]['details'][0]['new'], '3');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_field_sizing_weight()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'sizing_weight' => '5',
            ])
            ->new_values([
                'sizing_weight' => '3',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing weight');
        $this->assertEquals($res[0]['details'][0]['new'], '3');
        $this->assertEquals($res[0]['details'][0]['old'], '5');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_remove_field_sizing_weight()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'sizing_weight' => '5',
            ])
            ->new_values([
                'sizing_weight' => null,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'sizing weight');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], '5');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_field_sizing_is_auto()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_UPDATED)
            ->old_values([
                'sizing_is_auto' => '0',
            ])
            ->new_values([
                'sizing_is_auto' => '1',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'auto size');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_note()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $note = $this->orderNoteBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($note)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'id' => $note->id,
                'order_id' => $model->id,
                'user_id' => 1,
                'text' => 'note 2',
                'is_pinned' => 1,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(2, count($res));
        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res[1]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertEquals($res[0]['details'][0]['new'], 'note 2');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[1]['details'][0]['field'], 'is pinned');
        $this->assertEquals($res[1]['details'][0]['new'], 'true');
        $this->assertNull($res[1]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_note()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $note = $this->orderNoteBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($note)
            ->event(Audit::EVENT_UPDATED)
            ->old_values([
                'text' => 'note 1',
            ])
            ->new_values([
                'text' => 'note 2',
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertEquals($res[0]['details'][0]['new'], 'note 2');
        $this->assertEquals($res[0]['details'][0]['old'], 'note 1');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_remove_note()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $note = $this->orderNoteBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($note)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'id' => $note->id,
                'order_id' => $model->id,
                'visibility' => null,
                'user_id' => 1,
                'text' => 'note 2',
                'is_pinned' => 1,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], 'note 2');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_clone_as_base()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $newOrder Order */
        $newOrder = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CLONED)
            ->new_values([
                'order_id' => $newOrder->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'order id');
        $this->assertEquals($res[0]['details'][0]['new'], $newOrder->id);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CLONED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_clone_as_cloned()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $baseOrder Order */
        $baseOrder = $this->orderBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CLONED)
            ->old_values([
                'order_id' => $baseOrder->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'order id');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $baseOrder->id);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CLONED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_client()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'client_id' => $client->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'client');
        $this->assertEquals($res[0]['details'][0]['new'], $client->full_name);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_client()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_UPDATED)
            ->new_values([
                'client_id' => $client->id,
            ])
            ->old_values([
                'client_id' => $client_2->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'client');
        $this->assertEquals($res[0]['details'][0]['new'], $client->full_name);
        $this->assertEquals($res[0]['details'][0]['old'], $client_2->full_name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_add_division()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'division_id' => $division->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'division');
        $this->assertEquals($res[0]['details'][0]['new'], $division->name);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_order_change_division()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $division_2 = $this->divisionBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'division_id' => $division->id,
            ])
            ->old_values([
                'division_id' => $division_2->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'division');
        $this->assertEquals($res[0]['details'][0]['new'], $division->name);
        $this->assertEquals($res[0]['details'][0]['old'], $division_2->name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Order');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
