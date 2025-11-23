<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Enums\Catalog\BuildingTypeEnum;
use App\Enums\Catalog\FlightTypeEnum;
use App\Enums\Catalog\ParkingTypeEnum;
use App\Models\Audit;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WaypointBuilder;
use Tests\Builders\Orders\WaypointNotesBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ByOrderWaypointTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected WaypointBuilder $waypointBuilder;
    protected WaypointNotesBuilder $waypointNotesBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->waypointBuilder = resolve(WaypointBuilder::class);
        $this->waypointNotesBuilder = resolve(WaypointNotesBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);

        parent::setUp();
    }

    /** @test */
    public function audit_change_type()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'type' => 'destination',
            ])
            ->old_values([
                'type' => 'pickup',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'type');
        $this->assertEquals($res[0]['details'][0]['old'], 'pickup');
        $this->assertEquals($res[0]['details'][0]['new'], 'destination');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_city()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'city' => 'Chicago',
                'miscs' => ['usedAutocomplete' => false],
            ])
            ->old_values([
                'city' => 'Chicago 1',
                'miscs' => null
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'city');
        $this->assertEquals($res[0]['details'][0]['old'], 'Chicago 1');
        $this->assertEquals($res[0]['details'][0]['new'], 'Chicago');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_building_type()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'building_type_id' => BuildingTypeEnum::Home(),
            ])
            ->old_values([
                'building_type_id' => BuildingTypeEnum::Apartment(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'building type');
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Apartment())
        );
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Home())
        );

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_remove_building_type()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'building_type_id' => null,
            ])
            ->old_values([
                'building_type_id' => BuildingTypeEnum::Apartment(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'building type');
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Apartment())
        );
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_parking_type()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'parking_type_id' => ParkingTypeEnum::No_parking(),
            ])
            ->old_values([
                'parking_type_id' => ParkingTypeEnum::Loading_dock(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'parking type');
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Loading_dock())
        );
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::No_parking())
        );

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_remove_parking_type()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'parking_type_id' => null,
            ])
            ->old_values([
                'parking_type_id' => ParkingTypeEnum::Loading_dock(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'parking type');
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Loading_dock())
        );
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_add_unit()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'ap' => '11',
            ])
            ->old_values([
                'ap' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'unit');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], '11');

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_unit()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'ap' => '11',
            ])
            ->old_values([
                'ap' => '3',
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'unit');
        $this->assertEquals($res[0]['details'][0]['new'], '11');
        $this->assertEquals($res[0]['details'][0]['old'], '3');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_remove_unit()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->old_values([
                'ap' => '11',
            ])
            ->new_values([
                'ap' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'unit');
        $this->assertEquals($res[0]['details'][0]['old'], '11');
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_add_stairs()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'flights_id' => FlightTypeEnum::Flight_4(),
            ])
            ->old_values([
                'flights_id' => null,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'stairs');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_4())
        );

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_stairs()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'flights_id' => FlightTypeEnum::Flight_4(),
            ])
            ->old_values([
                'flights_id' => FlightTypeEnum::Flight_3(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'stairs');
        $this->assertEquals(
            $res[0]['details'][0]['new'],
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_4())
        );
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_3())
        );

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_remove_stairs()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'flights_id' => 0,
            ])
            ->old_values([
                'flights_id' => FlightTypeEnum::Flight_4(),
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'stairs');
        $this->assertEquals(
            $res[0]['details'][0]['old'],
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_4())
        );
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_has_elevator()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'has_elevator' => 1,
            ])
            ->old_values([
                'has_elevator' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'has elevator');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_address_as_manual()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'address' => "Village Grove Drive, Elk Grove Village, Elk Grove, IL 60007, USA",
                'lat' => "42.01136169",
                'lng' => "-88.00205890",
            ])
            ->old_values([
                'address' => "Elk Grove Village, IL 60007, USA",
                'lat' => "42.01136169",
                'lng' => "-88.00205890",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'address');
        $this->assertEquals($res[0]['details'][0]['old'], 'Elk Grove Village, IL 60007, USA');
        $this->assertEquals($res[0]['details'][0]['new'], 'Village Grove Drive, Elk Grove Village, Elk Grove, IL 60007, USA');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_state_as_manual()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'state' => "IL",
            ])
            ->old_values([
                'state' => "CA",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'state');
        $this->assertEquals($res[0]['details'][0]['old'], 'CA');
        $this->assertEquals($res[0]['details'][0]['new'], 'IL');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_zip()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'zip' => "60008",
            ])
            ->old_values([
                'zip' => "60007",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'zip');
        $this->assertEquals($res[0]['details'][0]['old'], '60007');
        $this->assertEquals($res[0]['details'][0]['new'], '60008');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_add_note()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();
        /** @var $note Order\WaypointNotes */
        $note = $this->waypointNotesBuilder
            ->waypoint($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($note)
            ->new_values([
                'user_id' => $note->user_id,
                'value' => $note->value,
                'waypoint_id' => $model->id,
                'id' => $note->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], $note->value);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type}) note");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_delete_note()
    {
        /** @var $model Order\Waypoint */
        $model = $this->waypointBuilder->create();
        /** @var $note Order\WaypointNotes */
        $note = $this->waypointNotesBuilder
            ->waypoint($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($note)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'user_id' => $note->user_id,
                'value' => $note->value,
                'waypoint_id' => $model->id,
                'id' => $note->id,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertEquals($res[0]['details'][0]['old'], $note->value);
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Waypoint ({$model->type}) note");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

}
