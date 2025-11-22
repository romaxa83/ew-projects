<?php

namespace App\Services\Orders;

use App\Contracts\Roles\HasGuardUser;
use App\Exceptions\Orders\OrderCategoryUsedException;
use App\Models\BaseHasTranslation;
use App\Models\Orders\Categories\OrderCategory;
use App\Services\BaseCrudDictionaryService;
use Illuminate\Database\Eloquent\Collection;

class OrderCategoryService extends BaseCrudDictionaryService
{
    public function getList(array $args, HasGuardUser $authUser): ?Collection
    {
        return OrderCategory::forGuard(
            !array_key_exists('for_edit', $args) || $args['for_edit'] === true ? $authUser : null
        )
            ->filter($args)
            ->orderBy('is_default')
            ->latest('sort')
            ->get();
    }

    public function deleteModel(OrderCategory $model): bool
    {
        $this->checkOffModel($model);

        $model->delete();

        return true;
    }

    /**
     * @param BaseHasTranslation|OrderCategory $model
     * @throws OrderCategoryUsedException
     */
    protected function checkOffModel(BaseHasTranslation|OrderCategory $model): void
    {
        if (!$model->orders()
            ->exists()) {
            return;
        }

        throw new OrderCategoryUsedException();
    }

    protected function getModel(): string
    {
        return OrderCategory::class;
    }
}
