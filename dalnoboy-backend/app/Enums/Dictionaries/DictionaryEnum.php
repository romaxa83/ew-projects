<?php

namespace App\Enums\Dictionaries;

use App\Models\Branches\Branch;
use App\Models\Clients\Client;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Dictionaries\Regulation;
use App\Models\Dictionaries\TireChangesReason;
use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use App\Models\Dictionaries\TireWidth;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Dictionaries\VehicleType;
use App\Models\Drivers\Driver;
use App\Models\Locations\Region;
use App\Models\Tires\Tire;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Vehicle;
use Core\Enums\BaseEnum;

/**
 * Class BanReasonsEnum
 * @package App\Enums\Dictionaries
 *
 * @method static static REGIONS()
 * @method static static CLIENTS()
 * @method static static DRIVERS()
 * @method static static SCHEMAS_VEHICLE()
 * @method static static VEHICLES()
 * @method static static VEHICLE_CLASSES()
 * @method static static VEHICLE_TYPES()
 * @method static static VEHICLE_MAKES()
 * @method static static VEHICLE_MODELS()
 * @method static static TIRE_MAKES()
 * @method static static TIRE_MODELS()
 * @method static static TIRE_TYPES()
 * @method static static TIRE_WIDTHS()
 * @method static static TIRE_HEIGHTS()
 * @method static static TIRE_DIAMETERS()
 * @method static static TIRE_SIZES()
 * @method static static TIRE_SPECIFICATIONS()
 * @method static static TIRE_RELATIONSHIP_TYPES()
 * @method static static TIRES()
 * @method static static INSPECTION_REASONS()
 * @method static static PROBLEMS()
 * @method static static REGULATIONS()
 * @method static static RECOMMENDATIONS()
 *
 */
class DictionaryEnum extends BaseEnum
{
    public const REGIONS = 'regions';
    public const CLIENTS = 'clients';
    public const DRIVERS = 'drivers';
    public const SCHEMAS_VEHICLE = 'schemasVehicle';
    public const VEHICLES = 'vehicles';
    public const VEHICLE_CLASSES = 'vehicleClasses';
    public const VEHICLE_TYPES = 'vehicleTypes';
    public const VEHICLE_MAKES = 'vehicleMakes';
    public const VEHICLE_MODELS = 'vehicleModels';
    public const TIRE_MAKES = 'tireMakes';
    public const TIRE_MODELS = 'tireModels';
    public const TIRE_TYPES = 'tireTypes';
    public const TIRE_WIDTHS = 'tireWidths';
    public const TIRE_HEIGHTS = 'tireHeights';
    public const TIRE_DIAMETERS = 'tireDiameters';
    public const TIRE_SIZES = 'tireSizes';
    public const TIRE_SPECIFICATIONS = 'tireSpecifications';
    public const TIRE_RELATIONSHIP_TYPES = 'tireRelationshipTypes';
    public const TIRES = 'tires';
    public const INSPECTION_REASONS = 'inspectionReasons';
    public const PROBLEMS = 'problems';
    public const REGULATIONS = 'regulations';
    public const RECOMMENDATIONS = 'recommendations';
    public const TIRE_CHANGES_REASONS = 'tireChangesReasons';
    public const BRANCHES = 'branches';

    public static function getConfig(): array
    {
        return [
            self::REGIONS => [
                'class' => Region::class,
                'active' => false,
                'updated' => false,
            ],
            self::CLIENTS => [
                'class' => Client::class,
                'active' => false,
            ],
            self::DRIVERS => [
                'class' => Driver::class,
                'active' => false,
            ],
            self::SCHEMAS_VEHICLE => [
                'class' => SchemaVehicle::class,
                'active' => false,
            ],
            self::VEHICLES => [
                'class' => Vehicle::class,
            ],
            self::VEHICLE_CLASSES => [
                'class' => VehicleClass::class,
            ],
            self::VEHICLE_TYPES => [
                'class' => VehicleType::class,
            ],
            self::VEHICLE_MAKES => [
                'class' => VehicleMake::class,
            ],
            self::VEHICLE_MODELS => [
                'class' => VehicleModel::class,
            ],
            self::TIRE_MAKES => [
                'class' => TireMake::class,
            ],
            self::TIRE_MODELS => [
                'class' => TireModel::class,
            ],
            self::TIRE_TYPES => [
                'class' => TireType::class,
            ],
            self::TIRE_WIDTHS => [
                'class' => TireWidth::class,
            ],
            self::TIRE_HEIGHTS => [
                'class' => TireHeight::class,
            ],
            self::TIRE_DIAMETERS => [
                'class' => TireDiameter::class,
            ],
            self::TIRE_SIZES => [
                'class' => TireSize::class,
            ],
            self::TIRE_SPECIFICATIONS => [
                'class' => TireSpecification::class,
            ],
            self::TIRE_RELATIONSHIP_TYPES => [
                'class' => TireRelationshipType::class,
            ],
            self::TIRES => [
                'class' => Tire::class,
            ],
            self::INSPECTION_REASONS => [
                'class' => InspectionReason::class,
            ],
            self::PROBLEMS => [
                'class' => Problem::class,
            ],
            self::REGULATIONS => [
                'class' => Regulation::class,
            ],
            self::RECOMMENDATIONS => [
                'class' => Recommendation::class,
            ],
            self::TIRE_CHANGES_REASONS => [
                'class' => TireChangesReason::class,
                'active' => false,
                'updated' => false,
            ],
            self::BRANCHES => [
                'class' => Branch::class,
            ]
        ];
    }

}
