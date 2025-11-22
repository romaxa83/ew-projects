<?php

namespace App\Services\Histories\BodyShop;

use App\Models\DiffableInterface;
use App\Services\Histories\HistoryHandlerInterface;
use Swaggest\JsonDiff\JsonDiff;

class InventoryHistoryHandler implements HistoryHandlerInterface
{
    private array $origin = [];
    private array $dirty = [];
    protected ?DiffableInterface $inventoryOld = null;
    protected ?DiffableInterface $inventoryNew = null;
    private ?string $comment = null;

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setOrigin(?DiffableInterface $diffable): self
    {
        $this->origin = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->inventoryOld = clone $diffable;

        return $this;
    }

    public function setDirty(?DiffableInterface $diffable): self
    {
        $this->dirty = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->inventoryNew = $diffable;

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

        if ($this->inventoryOld && $replaced = $this->mapsAndReplacingKeyWithValue($key, $this->inventoryOld)) {
            $old = $replaced;
        }

        if ($this->inventoryNew && $replaced = $this->mapsAndReplacingKeyWithValue($key, $this->inventoryNew, $this->comment)) {
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
            'name',
            'stock_number',
            'quantity',
            'price_retail',
            'notes',
            'category_id',
            'supplier_id',
            'unit_id',
        ];

        return in_array($key, $keys, true);
    }

    private function mapsAndReplacingKeyWithValue(string $key, DiffableInterface $inventory, ?string $comment = null): ?string
    {
        $maps = [
            'category_id' =>  $inventory->category->name ?? null,
            'supplier_id' => $inventory->supplier->name ?? null,
            'quantity' => $comment ? sprintf('%s (%s)', $inventory->quantity ?? null, $comment) : $inventory->quantity ?? null,
            'unit_id' => $inventory->unit->name ?? null,
        ];

        return $maps[$key] ?? null;
    }
}
