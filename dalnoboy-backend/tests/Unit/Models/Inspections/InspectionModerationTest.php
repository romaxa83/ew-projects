<?php


namespace Tests\Unit\Models\Inspections;


use App\Models\Inspections\Inspection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionModerationTest extends TestCase
{
    use DatabaseTransactions;

    private Inspection $inspection;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspection = Inspection::factory()
            ->create();
    }

    public function test_not_moderation(): void
    {
        $this->assertFalse($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_tire_size(): void
    {
        $tireSize = $this->inspection->inspectionTires[0]->tire->specification->tireSize;

        $tireSize->is_moderated = false;
        $tireSize->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_tire_model(): void
    {
        $tireModel = $this->inspection->inspectionTires[0]->tire->specification->tireModel;

        $tireModel->is_moderated = false;
        $tireModel->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_tire_make(): void
    {
        $tireMake = $this->inspection->inspectionTires[0]->tire->specification->tireMake;

        $tireMake->is_moderated = false;
        $tireMake->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_tire_specification(): void
    {
        $tireSpecification = $this->inspection->inspectionTires[0]->tire->specification;

        $tireSpecification->is_moderated = false;
        $tireSpecification->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_tire(): void
    {
        $tire = $this->inspection->inspectionTires[0]->tire;

        $tire->is_moderated = false;
        $tire->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_driver_client(): void
    {
        $client = $this->inspection->driver->client;

        $client->is_moderated = false;
        $client->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_driver(): void
    {
        $driver = $this->inspection->driver;

        $driver->is_moderated = false;
        $driver->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_vehicle_client(): void
    {
        $client = $this->inspection->vehicle->client;

        $client->is_moderated = false;
        $client->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }

    public function test_moderated_because_of_vehicle(): void
    {
        $vehicle = $this->inspection->vehicle;

        $vehicle->is_moderated = false;
        $vehicle->save();

        $this->assertTrue($this->inspection->shouldModerated());
    }
}
