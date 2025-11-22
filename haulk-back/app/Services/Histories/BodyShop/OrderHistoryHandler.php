<?php

namespace App\Services\Histories\BodyShop;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Payment;
use App\Models\DiffableInterface;
use App\Services\Histories\HistoryHandlerInterface;
use Carbon\Carbon;
use Swaggest\JsonDiff\JsonDiff;

class OrderHistoryHandler implements HistoryHandlerInterface
{
    private array $origin = [];
    private array $dirty = [];
    protected DiffableInterface $orderOld;
    protected DiffableInterface $orderNew;

    public function setOrigin(DiffableInterface $diffable): self
    {
        $this->origin = $diffable->getAttributesForDiff();
        $this->orderOld = clone $diffable;

        return $this;
    }

    public function setDirty(DiffableInterface $diffable): self
    {
        $this->dirty = $diffable->getAttributesForDiff();
        $this->orderNew = $diffable;

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

    private function skipped($key): bool
    {
        $keys = [
            'created_at',
            'updated_at',
            'status_changed_at',
            'is_billed',
            'billed_at',
            'paid_at',
            'is_paid',
            'total_amount',
            'paid_amount',
            'debt_amount',

            'typesOfWork.updated_at',
            'typesOfWork.created_at',
            'typesOfWork.order_id',

            'typesOfWork.inventories.updated_at',
            'typesOfWork.inventories.created_at',
            'typesOfWork.inventories.type_of_work_id',

            'attachments.file_name',
            'attachments.updated_at',
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

    private function generateComparisonsChanges(&$comparisons, $attributes, &$keys, $old): void
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute === $old) {
                continue;
            }
            $keys[] = $key;
            if ($attribute !== null && (is_object($attribute) || is_array($attribute))) {
                $this->generateComparisonsChanges($comparisons, $attribute, $keys, $old);
            } else {
                $keysForValid = array_filter($keys, function ($n) {
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
        if($oldRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->orderOld))
        {
            $newRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->orderNew);

            unset($keys[array_key_last($keys)]);

            $newKey = array_key_last($newRelation);
            $keys[] = $newKey;

            $comparisons[implode('.', $keys)] = [
                'new' => $newRelation[$newKey] ?? null,
                'old' => $oldRelation[$newKey] ?? null,
                'type' => $type
            ];
        } elseif($oldRelation = $this->mapsAndReplacingKeyWithValueForCollection(implode('.', $newKeys), $old)){
            $newRelation = $this->mapsAndReplacingKeyWithValueForCollection(implode('.', $newKeys), $attribute);

            unset($keys[array_key_last($keys)]);

            $newKey = array_key_last($newRelation);
            $keys[] = $newKey;

            $comparisons[implode('.', $keys)] = [
                'new' => $newRelation[$newKey] ?? null,
                'old' => $oldRelation[$newKey] ?? null,
                'type' => $type
            ];
        } else {
            $comparisons[implode('.', $keys)] = [
                'old' => $old,
                'new' => $attribute,
                'type' => $type
            ];
        }
    }

    private function mapsValidKeys($key): bool
    {
        $keys = [
            'comments.comment',

            'typesOfWork.name',
            'typesOfWork.duration',
            'typesOfWork.hourly_rate',

            'typesOfWork.inventories',

            'typesOfWork.inventories.inventory_id',
            'typesOfWork.inventories.price',
            'typesOfWork.inventories.quantity',

            'attachments.name',

            'payments.amount',
            'payments.payment_method',
            'payments.payment_date',
            'payments.notes',
            'payments.reference_number',
        ];

        return in_array($key, $keys, true);
    }

    private function mapsAndReplacingKeyWithValue($key, $order): array
    {
        $maps = [
            'mechanic_id' =>  [
                'mechanic_id' => $order->mechanic->full_name ?? null
            ],
            'driver_id' => [
                'driver_id' => $order->driver->full_name ?? null
            ],
            'truck_id' => [
                'truck_id' => $order->truck ? $order->truck->vin : null,
            ],
            'trailer_id' => [
                'trailer_id' => $order->trailer ? $order->trailer->vin : null,
            ],
        ];

        return $maps[$key] ?? [];
    }

    private function mapsAndReplacingKeyWithValueForCollection($key, $id): array
    {
        if ($key === 'typesOfWork.inventories.inventory_id') {
            return [
                'inventory_id' => Inventory::find($id)->name ?? null,
            ];
        }

        if ($key === 'payments.payment_method') {
            return [
                'payment_method' => Payment::PAYMENT_METHODS[$id] ?? $id,
            ];
        }

        if ($key === 'payments.payment_date') {
            return [
                'payment_date' => $id ? (new Carbon(strtotime($id)))->format('m/d/Y') : $id,
            ];
        }

        return [];
    }
}
