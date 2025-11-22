<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\VehicleCollection;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Database\Factories\Orders\VehicleFactory;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int|null order_id
 * @property int type_id
 * @property string|null vin
 * @property string|null year
 * @property string|null make
 * @property string|null model
 * @property string|null color
 * @property string|null license_plate
 * @property string|null temporary_plate
 * @property string|null odometer
 * @property string|null unit_number
 * @property bool inop
 * @property bool enclosed
 *
 * @property mixed|null delivery_inspection_id
 * @property mixed|null pickup_inspection_id
 *
 * @see Vehicle::order()
 * @property Order order
 *
 * @see Vehicle::deliveryInspection()
 * @property Inspection deliveryInspection
 *
 * @see Vehicle::pickupInspection()
 * @property Inspection pickupInspection
 * @method VehicleCollection|static[] get($fields = [])
 *
 * @method static VehicleFactory factory(...$parameters)
 */
class Vehicle extends Model implements DiffableInterface
{
    use Diffable;
    use HasFactory;

    public const VEHICLE_TYPE_ATV = 1;
    public const VEHICLE_TYPE_BOAT = 2;
    public const VEHICLE_TYPE_COUPE_2 = 3;
    public const VEHICLE_TYPE_MOTORCYCLE = 7;
    public const VEHICLE_TYPE_PICKUP_4 = 8;
    public const VEHICLE_TYPE_PICKUP_2 = 9;
    public const VEHICLE_TYPE_SEDAN = 11;
    public const VEHICLE_TYPE_SUV = 12;
    public const VEHICLE_TYPE_TRAILER_BUMPER = 13;
    public const VEHICLE_TYPE_TRUCK_DAYCAB = 16;
    public const VEHICLE_TYPE_VAN = 18;
    public const VEHICLE_TYPE_OTHER = 19;

    public const VEHICLE_TYPES = [
        self::VEHICLE_TYPE_ATV => 'ATV',
        self::VEHICLE_TYPE_BOAT => 'Boat',
        self::VEHICLE_TYPE_COUPE_2 => 'Coupe (2 doors)',
        4 => 'Freight',
        5 => 'Heavy Machinery',
        6 => 'Livestock',
        self::VEHICLE_TYPE_MOTORCYCLE => 'Motorcycle',
        self::VEHICLE_TYPE_PICKUP_4 => 'Pickup (4 Doors)',
        self::VEHICLE_TYPE_PICKUP_2 => 'Pickup (2 Doors)',
        10 => 'RV',
        self::VEHICLE_TYPE_SEDAN => 'Sedan',
        self::VEHICLE_TYPE_SUV => 'SUV',
        self::VEHICLE_TYPE_TRAILER_BUMPER => 'Trailer (Bumper Pull)',
        14 => 'Trailer (Gooseneck)',
        15 => 'Trailer (5th Wheel)',
        self::VEHICLE_TYPE_TRUCK_DAYCAB => 'Truck (daycab)',
        17 => 'Truck (with sleeper)',
        self::VEHICLE_TYPE_VAN => 'Van',
        self::VEHICLE_TYPE_OTHER => 'Other',
    ];

    public const DEFAULT_TYPE = 11;

    public const TABLE_NAME = 'vehicles';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'inop',
        'enclosed',
        'vin',
        'year',
        'make',
        'model',
        'type_id',
        'color',
        'license_plate',
        'odometer',
        'stock_number',
    ];

    protected $casts = [
        'old_values' => 'array',
        'inop' => 'bool',
        'enclosed' => 'bool',
    ];

    public static function getTypesList(): array
    {
        $data = [];

        foreach (self::VEHICLE_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'title' => $v,
            ];
        }

        return $data;
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function pickupInspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'pickup_inspection_id');
    }

    /**
     * @return BelongsTo
     */
    public function deliveryInspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'delivery_inspection_id');
    }

    public function getTypeNameAttribute(): string
    {
        return self::VEHICLE_TYPES[$this->type_id] ?? 'unknown';
    }

    public function getPickupDamagePhoto(): ?string
    {
        if ($this->pickupInspection) {
            $image = $this->pickupInspection->getFirstMedia(
                env('HARDCODE_OLD_DAMAGE_PHOTO')
                    ? Order::INSPECTION_DAMAGE_COLLECTION_NAME
                    : Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME
            );

            if ($image) {
                return $image->getFullUrl();
            }
        }

        return null;
    }

    public function getDeliveryDamagePhoto(): ?string
    {
        if ($this->deliveryInspection) {
            $image = $this->deliveryInspection->getFirstMedia(
                env('HARDCODE_OLD_DAMAGE_PHOTO')
                    ? Order::INSPECTION_DAMAGE_COLLECTION_NAME
                    : Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME
            );

            if ($image) {
                return $image->getFullUrl();
            }
        }

        return null;
    }

    public function getTypeImagePath(): string
    {
        $publicDir = config('orders.vehicles.types.public_dir');
        $extension = config('orders.vehicles.types.extension');

        $path = sprintf(
            '%s/%s.%s',
            $publicDir,
            $this->type_id,
            $extension
        );

        if (file_exists(public_path($path))) {
            return $path;
        }

        return sprintf(
            '%s/%s.%s',
            $publicDir,
            self::DEFAULT_TYPE,
            $extension
        );
    }

    public function getPhotoLimit(): int
    {
        return config('orders.inspection.max_photo');
    }

    public function getMinPhotoCount(): int
    {
        switch ($this->type_id) {
            case self::VEHICLE_TYPE_MOTORCYCLE:
            case self::VEHICLE_TYPE_ATV:
                return config('orders.inspection.min_photo.motorcycle');

            case self::VEHICLE_TYPE_BOAT:
                return config('orders.inspection.min_photo.boat');
        }

        return config('orders.inspection.min_photo.other');
    }

    public function givePickupInspection(): void
    {
        // create pickup inspection if not exist
        if (!$this->pickup_inspection_id) {
            $pickupInspectionModel = Inspection::query()->create(
                [
                    'vin' => $this->vin,
                ]
            );

            $pickupInspectionModel->has_vin_inspection = true;
            $pickupInspectionModel->save();

            $this->pickupInspection()->associate($pickupInspectionModel);
            $this->save();
        } else {
            $pickupInspectionModel = $this->pickupInspection;
        }

        $pickupInspectionModel->setCompletedFields();
        $pickupInspectionModel->save();

        // create empty delivery inspection if not exist
        if (!$this->delivery_inspection_id) {
            $deliveryInspectionModel = Inspection::create(
                [
                    'vin' => $this->vin,
                ]
            );

            $deliveryInspectionModel->has_vin_inspection = true;
            $deliveryInspectionModel->save();

            $this->deliveryInspection()->associate($deliveryInspectionModel);
            $this->save();
        }
    }

    public function giveDeliveryInspection($orderAssigned = false): void
    {
        // create pickup inspection if order isAssigned
        if ($orderAssigned) {
            if (!$this->pickup_inspection_id) {
                $pickupInspectionModel = Inspection::create(
                    [
                        'vin' => $this->vin,
                    ]
                );

                $pickupInspectionModel->has_vin_inspection = true;
                $pickupInspectionModel->save();

                $this->pickupInspection()->associate($pickupInspectionModel);
                $this->save();
            } else {
                $pickupInspectionModel = $this->pickupInspection;
            }

            $pickupInspectionModel->setCompletedFields();
            $pickupInspectionModel->save();
        }

        // create delivery inspection if not exist
        if (!$this->delivery_inspection_id) {
            $deliveryInspectionModel = Inspection::create(
                [
                    'vin' => $this->vin,
                ]
            );

            $deliveryInspectionModel->has_vin_inspection = true;
            $deliveryInspectionModel->save();

            $this->deliveryInspection()->associate($deliveryInspectionModel);
            $this->save();
        } else {
            $deliveryInspectionModel = $this->deliveryInspection;
        }

        $deliveryInspectionModel->setCompletedFields();
        $deliveryInspectionModel->save();
    }

    /**
     * @throws Exception
     */
    public function createInspectionsIfNotExist(): void
    {
        try {
            DB::beginTransaction();

            if (!$this->pickup_inspection_id) {
                $pickupInspectionModel = Inspection::create();
                $this->pickupInspection()->associate($pickupInspectionModel);
            }

            if (!$this->delivery_inspection_id) {
                $deliveryInspectionModel = new Inspection();
                $deliveryInspectionModel->has_vin_inspection = true;
                $deliveryInspectionModel->save();
                $this->deliveryInspection()->associate($deliveryInspectionModel);
            }

            $this->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function newCollection(array $models = []): VehicleCollection
    {
        return VehicleCollection::make($models);
    }

    public function preserveOldValues(): void
    {
        if (!$this->old_values) {
            $this->old_values = [
                'vin' => $this->vin,
            ];
        }
    }

    public function restoreOldValues(): void
    {
        $this->fill(
            [
                'vin' => $this->old_values['vin'] ?? $this->vin,
//                'vin' => $this->old_values['vin'] ?? null,
            ]
        );

        $this->old_values = null;
    }
}
