<?php

namespace App\Services\Histories;

use App\Models\DiffableInterface;
use App\Models\Vehicles\Vehicle;
use Swaggest\JsonDiff\JsonDiff;

class VehicleHistoryHandler implements HistoryHandlerInterface
{
    private array $origin = [];
    private array $dirty = [];
    protected ?DiffableInterface $vehicleOld = null;
    protected ?DiffableInterface $vehicleNew = null;

    public function setOrigin(?DiffableInterface $diffable): self
    {
        $this->origin = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->vehicleOld = $diffable ?clone $diffable : null;

        return $this;
    }

    public function setDirty(?DiffableInterface $diffable): self
    {
        $this->dirty = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->vehicleNew = $diffable;

        return $this;
    }

    public function start(): array
    {
        $jsonDiff = new JsonDiff(
            $this->origin,
            $this->dirty,
            JsonDiff::COLLECT_MODIFIED_DIFF + JsonDiff::TOLERATE_ASSOCIATIVE_ARRAYS
        );

        $comparisons = [];
        $keys = [];
        foreach ($jsonDiff->getModifiedDiff() as $item) {
            $flatStructure = [str_replace('/', '.', trim($item->path, '/')) => $item->new ?? null];

            if (!$flatStructure) {
                continue;
            }

            $array = array_undot($flatStructure);
            $this->generateComparisonsChanges($comparisons, $array, $keys,$item->original ?? null,);
        }

        if ($added = $jsonDiff->getAdded()) {
            $this->generateComparisons($comparisons, $added);
        }

        if ($removed = $jsonDiff->getRemoved()) {
            $this->generateComparisons($comparisons, $removed, false);
        }

        return $comparisons;
    }

    private function skipped(string $key): bool
    {
        $keys = [
            'created_at',
            'updated_at',
        ];

        return in_array($key, $keys, true);
    }

    private function generateComparisons(&$comparisons, $attributes, $added = true, &$keys = []): void
    {
        foreach ($attributes as $key => $attribute) {
            if (!$attribute) {
                continue;
            }
            $keys[] = $key;
            if (is_object($attribute) || is_array($attribute)) {
                $this->generateComparisons($comparisons, $attribute, $added, $keys);
            } else {
                $keysForValid = array_filter($keys, function ($n){
                    return !is_numeric($n);
                });

                if($this->mapsValidKeys(implode('.', $keysForValid)))
                {
                    if(!$added) {
                        $this->setAttribute($comparisons, null, $keys, $keysForValid, $attribute, 'removed');
                    } else {
                        $this->setAttribute($comparisons, $attribute, $keys, $keysForValid, null, 'added');
                    }
                }
            }

            array_pop($keys);
        }
    }

    private function generateComparisonsChanges(&$comparisons, array $attributes, array &$keys, $old): void
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute === $old) {
                continue;
            }
            $keys[] = $key;
            if (is_object($attribute) || is_array($attribute)) {
                $this->generateComparisonsChanges($comparisons, $attribute, $keys, $old);
            } else {
                $keysForValid = array_filter($keys, function ($n){
                    return !is_numeric($n);
                });

                if (!$this->skipped(implode('.', $keysForValid))) {
                    $this->setAttribute($comparisons, $attribute, $keys, $keysForValid, $old);
                }
            }

            array_pop($keys);
        }
    }

    private function setAttribute(&$comparisons, $attribute, $keys, $newKeys, $old, $type = 'updated'): void
    {
        $key = implode('.', $keys);

        if ($this->vehicleOld && $replaced = $this->mapsAndReplacingKeyWithValue($key, $this->vehicleOld)) {
            $old = $replaced;
        }

        if ($this->vehicleNew && $replaced = $this->mapsAndReplacingKeyWithValue($key, $this->vehicleNew)) {
            $attribute = $replaced;
        }

        $comparisons[$key] = [
            'old' => $old,
            'new' => $attribute,
            'type' => $type,
        ];
    }

    private function mapsValidKeys(string $key): bool
    {
        $keys = [
            'unit_number',
            'vin',
            'make',
            'model',
            'year',
            'license_plate',
            'temporary_plate',
            'notes',
            'owner_id',
            'driver_id',
            'customer_id',
            'type',
            'color',
            'registration_number',
            'registration_date',
            'registration_expiration_date',
            'inspection_date',
            'inspection_expiration_date',
            'comments.comment',
            'attachments.name',
            'inspection_file.name',
            'registration_file.name',
            'gps_device_id',
        ];

        return in_array($key, $keys, true);
    }

    private function mapsAndReplacingKeyWithValue(string $key, DiffableInterface $vehicle): ?string
    {
        $maps = [
            'owner_id' =>  $vehicle->owner ? $vehicle->owner->full_name : null,
            'driver_id' => $vehicle->driver ? $vehicle->driver->full_name : null,
            'customer_id' => $vehicle->customer ? $vehicle->customer->getFullName() : null,
            'type' => Vehicle::VEHICLE_TYPES[$vehicle->type] ?? null,
            'gps_device_id' => $vehicle->gpsDevice ? $vehicle->gpsDevice->imei : null,
        ];

        return $maps[$key] ?? null;
    }
}
