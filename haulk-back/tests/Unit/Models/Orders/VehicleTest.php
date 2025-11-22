<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_create_vehicle_by_factory()
    {
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::factory()->create();

        $this->assertDatabaseHas(
            Vehicle::TABLE_NAME,
            [
                'id' => $vehicle->id
            ]
        );
    }

    /**
     * @param $type
     * @param $expected
     * @dataProvider itGetCorrectUrlForCurrentVehicleTypeDataProvider
     */
    public function test_it_get_correct_url_for_current_vehicle_type($type, $expected)
    {
        $vehicle = new Vehicle();
        $vehicle->type_id = $type;

        $this->assertEquals($expected, $vehicle->getTypeImagePath());
    }

    public function itGetCorrectUrlForCurrentVehicleTypeDataProvider()
    {
        $path = 'vehicle-schemes/';
        $extension = 'png';

        return [
            [1, $path . '1.' . $extension],
            [2, $path . '2.' . $extension],
            [null, $path . '11.' . $extension],
            [200, $path . '11.'. $extension]
        ];
    }

}
