<?php

namespace App\Services\Catalog\Categories;

use App\Collections\Catalog\Categories\CategoryStorage;
use App\Models\Catalog\Categories\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryStorageService
{
    protected ?CategoryStorage $storage = null;

    public function getCategoriesAsTree(?bool $active = null, array $args = []): Collection
    {
        $categories = $this->getCategories($active, $args);

        return $this->buildTree($categories);
    }

    protected function getCategories(?bool $active = null, array $args = []): Collection
    {
//        dd($args);
        return Category::query()
            ->when(!is_null($active), static fn(Builder $b) => $b->where('active', $active))
            ->when(
                !(isset($args['with_olmo']) && $args['with_olmo']),
                static fn($b) => $b->cooper()
            )
            ->when(
                !isset($args['with_spares']) || (isset($args['with_spares']) && $args['with_spares'] == false),
                static fn($b) => $b->where('slug', '!=', 'spares')
            )
            ->with('translation')
            ->latest('sort')
            ->get()
            ->keyBy('id');
    }

    protected function buildTree(Collection $items): Collection
    {
        $result = [];

        $this->mapForTree($items, $result);

        return collect($result[null] ?? []);
    }

    protected function mapForTree(Collection $items, array &$result = []): void
    {
        foreach ($items as $item) {
            $result[$item->parent_id][] = $item;
        }

        foreach ($items as $item) {
            if (isset($result[$item->id])) {
                $item->setRelation('children', $result[$item->id]);
            } else {
                $item->setRelation('children', null);
            }

            if ($item->parent_id) {
                $item->setRelation('parent', $items->get($item->parent_id));
            } else {
                $item->setRelation('parent', null);
            }
        }
    }

    public function getTreeForCategory(?Category $category, ?bool $active = null, array $args = []): ?Category
    {
        if (!$category) {
            return null;
        }

        $categories = $this->getCategories($active, $args);
//        dd($category);

        $this->mapForTree($categories);

        return $categories->get($category->id);
    }

    public function getTotalCountForCategory(Category $category): int
    {
        return $this->generate()->find($category)?->getTotalCount() ?? 0;
    }

    public function generate(): CategoryStorage
    {
        if (is_null($this->storage)) {
            $this->storage = $this->generateStorage();
        }

        return $this->storage;
    }

    protected function generateStorage(): CategoryStorage
    {
        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->active()
            ->oldest('sort')
            ->getQuery()
            ->get();

        return new CategoryStorage($categories->toBase());
    }

    public function clear(): self
    {
        $this->storage = null;

        return $this;
    }
}
