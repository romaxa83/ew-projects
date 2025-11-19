<?php

declare(strict_types=1);

namespace Wezom\Core\Services;

use Illuminate\Database\Eloquent\Model;

class SortService
{
    public function saveSort(
        Model $modelName,
        array $items,
        int $offset = 0,
        string $fieldName = 'sort',
        string $parentFieldName = 'parent_id',
        string|int|null $parentId = null,
    ): void {
        foreach ($items as $sort => $item) {
            $id = $item['id'];

            /** @var Model $model */
            $model = $modelName::find($id);
            $model->forceFill([$fieldName => $sort + $offset])
                ->update([$parentFieldName => $parentId]);

            if ($children = data_get($item, 'children')) {
                $this->saveSort($modelName, $children, 0, $fieldName, $parentFieldName, $id);
            }
        }
    }
}
