<?php

namespace App\Services\Vehicles;

use App\Models\Orders\Vehicle;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Log;
use Throwable;

class VpicVinDecodeService implements VinDecodeService
{
    /**
     * @param string $vin
     * @return array|null[]
     * @throws Throwable
     */
    public function decodeVin(string $vin): array
    {
        $data = [
            'make' => null,
            'model' => null,
            'year' => null,
            'type_id' => null,
        ];

        if (preg_match('/[a-z0-9]{17}/i', $vin)) {
            $client = new GuzzleClient();

            try {
                $res = $client->request(
                    'GET',
                    sprintf(config('vehicles.vpic.url'), $vin),
                    [
                        RequestOptions::CONNECT_TIMEOUT => config('vehicles.vpic.connect_timeout'),
                        RequestOptions::TIMEOUT => config('vehicles.vpic.request_timeout'),
                    ]
                );

                if ($res->getStatusCode() == 200) {
                    $decoded = json_decode($res->getBody(), true);

                    if (isset($decoded['Results'][0]) && is_array($decoded['Results'][0])) {
                        $data = [
                            'make' => $decoded['Results'][0]['Make'] ?? null,
                            'model' => $decoded['Results'][0]['Model'] ?? null,
                            'year' => $decoded['Results'][0]['ModelYear'] ?? null,
                            'type_id' => null,
                        ];

                        if (isset($decoded['Results'][0]['BodyClass'])) {
                            $data['type_id'] = $this->getTypeFromVinData(
                                $decoded['Results'][0]['BodyClass'],
                                $decoded['Results'][0]['Doors'] ?? null
                            );
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::error($e);
                throw new Exception(
                    trans('VIN decoder service is not available now, please enter vehicle data manually.')
                );
            }
        }

        return $data;
    }

    private function getTypeFromVinData($body_class = null, $doors = null): ?int
    {
        switch ($body_class) {
            case 'Sedan/Saloon':
            case 'Convertible/Cabriolet':
            case 'Low Speed Vehicle (LSV) / Neighborhood Electric Vehicle (NEV)':
            case 'Hatchback/Liftback/Notchback':
            case 'Roadster':
            case 'Wagon':
            case 'Limousine':
                return Vehicle::VEHICLE_TYPE_SEDAN;

            case 'Sport Utility Vehicle (SUV)/Multi-Purpose Vehicle (MPV)':
            case 'Crossover Utility Vehicle (CUV)':
            case 'Sport Utility Truck (SUT)':
                return Vehicle::VEHICLE_TYPE_SUV;

            case 'Cargo Van':
            case 'Minivan':
            case 'Van':
                return Vehicle::VEHICLE_TYPE_VAN;

            case 'Coupe':
                return Vehicle::VEHICLE_TYPE_COUPE_2;

            case 'Truck-Tractor':
                return Vehicle::VEHICLE_TYPE_TRUCK_DAYCAB;

            case 'Motorcycle - Standard':
            case 'Motorcycle - Scooter':
            case 'Motorcycle - Side Car':
            case 'Motorcycle - Custom':
            case 'Motorcycle - Street':
            case 'Motorcycle - Enclosed Three Wheeled / Enclosed Autocycle':
            case 'Motorcycle - Unenclosed Three Wheeled / Open Autocycle':
            case 'Motorcycle - Moped':
            case 'Motorcycle - Cross Country':
            case 'Motorcycle - Underbone':
            case 'Motorcycle - Competition':
            case 'Motorcycle - Unknown Body Class':
            case 'Motorcycle - Sport':
            case 'Motorcycle - Touring / Sport Touring':
            case 'Motorcycle - Cruiser':
            case 'Motorcycle - Trike':
            case 'Motorcycle - Dual Sport / Adventure / Supermoto / On/Off-road':
            case 'Motorcycle - Small / Minibike':
            case 'Off-road Vehicle - All Terrain Vehicle (ATV) (Motorcycle-style)':
                return Vehicle::VEHICLE_TYPE_MOTORCYCLE;

            case 'Trailer':
                return Vehicle::VEHICLE_TYPE_TRAILER_BUMPER;

            case 'Incomplete - Cutaway':
            case 'Incomplete - Chassis Cab (Single Cab)':
            case 'Incomplete - Glider':
            case 'Incomplete':
            case 'Incomplete - Stripped Chassis':
            case 'Incomplete - Chassis Cab (Double Cab)':
            case 'Incomplete - Chassis Cab (Double Cab) ':
            case 'Incomplete - School Bus Chassis':
            case 'Incomplete - Commercial Bus Chassis':
            case 'Incomplete - Chassis Cab (Number of Cab Unknown)':
            case 'Incomplete - Transit Bus Chassis':
            case 'Incomplete - Motor Coach Chassis':
            case 'Incomplete - Shuttle Bus Chassis':
            case 'Incomplete - Motor Home Chassis':
            case 'Incomplete - Bus Chassis':
            case 'Incomplete - Commercial Chassis':
            case 'Incomplete - Trailer Chassis ':
                return Vehicle::VEHICLE_TYPE_PICKUP_4;

            case 'Pickup':
            case 'Truck':
                if ($doors && (int) $doors === 2) {
                    return Vehicle::VEHICLE_TYPE_PICKUP_2;
                }

                return Vehicle::VEHICLE_TYPE_PICKUP_4;

            case 'Bus':
            case 'Streetcar / Trolley':
            case 'Bus - School Bus':
            case 'Off-road Vehicle - Dirt Bike / Off-Road':
            case 'Off-road Vehicle - Enduro (Off-road long distance racing)':
            case 'Off-road Vehicle - Go Kart':
            case 'Off-road Vehicle - Snowmobile':
            case 'Off-road Vehicle - Recreational Off-Road Vehicle (ROV)':
            case 'Motorhome':
            case 'Step Van / Walk-in Van':
            case 'Off-road Vehicle - Motocross (Off-road short distance, closed track racing)':
            case 'Off-road Vehicle - Motocross (Off-road short distance, closed track racing) ':
            case 'Off-road Vehicle - Golf Cart':
            case 'Off-road Vehicle - Farm Equipment':
            case 'Off-road Vehicle - Construction Equipment':
                return Vehicle::VEHICLE_TYPE_OTHER;
        }

        return null;
    }
}
