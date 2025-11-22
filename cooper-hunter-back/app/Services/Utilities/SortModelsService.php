<?php

namespace App\Services\Utilities;

use App\Exceptions\Utilities\Sorting\ModelObjectNotFoundException;
use App\Models\BaseModel;

class SortModelsService
{
    /**
     * @param BaseModel $model
     * @param array $ids
     * @return bool
     */
    public function sort(BaseModel $model, array $ids): bool
    {
        $primaryKey = $model->getKeyName();

        $modelObjects = $model::query()
            ->whereKey($ids)
            ->get();

        $sortIndexes = $modelObjects
            ->pluck('sort')
            ->toArray();

        sort($sortIndexes);

        $index = count($sortIndexes) - 1;
        foreach ($ids as $id) {
            $modelObject = $modelObjects->sole($primaryKey, (int)$id);
            if (!$modelObject) {
                throw new ModelObjectNotFoundException($id);
            }
            $modelObject->sort = $sortIndexes[$index];
            $modelObject->save();
            if(method_exists($modelObject, 'setRelationSort')){
                $modelObject->setRelationSort();
            }
            $index--;
        }

        return true;
    }
}
