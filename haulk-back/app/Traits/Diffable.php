<?php

namespace App\Traits;


trait Diffable
{
    public function getAttributesForDiff(): array
    {
        $resultGetDirty = $this->getAttributes();

        foreach ($this->getRelationsForDiff() as $relationName => $relation) {
            if(is_array($relation)) {
                $resultGetDirty[$relationName] = $relation;
            } elseif($relation) {
                $resultGetDirty[$relationName] = $relation->getAttributesForDiff();
            }
        }

        return $resultGetDirty;
    }

    /**
     * @return Diffable[]
     */
    public function getRelationsForDiff(): array
    {
        return [];
    }
}
