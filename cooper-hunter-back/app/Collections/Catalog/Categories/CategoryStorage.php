<?php

namespace App\Collections\Catalog\Categories;

use App\Entities\Catalog\Categories\CategorySimple;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\Category as CategoryModel;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Products\Product;
use Exception;
use Illuminate\Support\Collection;
use RuntimeException;

class CategoryStorage
{
    private array $parents = [];

    private array $productCounters = [];

    private CategoryEntityCollection $tree;

    private CategoryEntityCollection $all;

    public function __construct(Collection $categories)
    {
        $this->all = CategoryEntityCollection::make();
        $this->tree = CategoryEntityCollection::make();

        $this->countProductsByCategory()
            ->addAll($categories)
            ->countProductsInList()
            ->fillTree();
    }

    protected function fillTree(): void
    {
        foreach ($this->getAll() as $category) {
            if ($category->hasParentId()) {
                if (is_null($parent = $this->all->get($category->getParentId()))) {
                    continue;
                }

                $parent->addChild($category);

                $category->setParent($parent);

                continue;
            }

            $this->tree->put($category->getId(), $category);
        }
    }

    public function getAll(): CategoryEntityCollection
    {
        return $this->all;
    }

    protected function countProductsInList(): self
    {
        foreach ($this->productCounters as $categoryId => $count) {
            /** @var CategorySimple $category */
            if ($category = $this->all->get($categoryId)) {
                $category->setProductCount($count);
            }
        }

        return $this;
    }

    protected function addAll(Collection $categories): self
    {
        foreach ($categories as $category) {
            $entity = new CategorySimple($category);
            $this->all->put($entity->getId(), $entity);
        }

        return $this;
    }

    protected function countProductsByCategory(): self
    {
        $this->productCounters = Product::query()
            ->active()
            ->getQuery()
            ->select(['id', 'category_id'])
            ->get()
            ->countBy('category_id')
            ->toArray();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function buildBreadcrumbs(Category|int $category): Collection
    {
        $category = $this->normalizeCategory($category);

        return Category::query()
            ->whereIn(Category::TABLE . '.id', $this->findParentIds($category))
            ->joinTranslation()
            ->addSelect(
                [
                    Category::TABLE . '.id',
                    Category::TABLE . '.parent_id',
                    Category::TABLE . '.slug',
                    CategoryTranslation::TABLE . '.title',
                ]
            )
            ->orderBy(Category::TABLE . '.parent_id')
            ->toBase()
            ->get();
    }

    /**
     * @throws Exception
     */
    protected function normalizeCategory(CategorySimple|CategoryModel|int $category): int
    {
        if ($category instanceof CategorySimple) {
            return $category->getId();
        }

        if ($category instanceof CategoryModel) {
            return $category->getKey();
        }

        if (is_int($category)) {
            return $category;
        }

        throw new RuntimeException('Parameter must be an integer!');
    }

    /**
     * @param CategorySimple|CategoryModel|int
     * @return array
     * @throws Exception
     */
    public function findParentIds($category): array
    {
        $id = $this->normalizeCategory($category);

        if (!isset($this->parents[$id])) {
            $parentIds = $this->findParentsChain($id)
                ->map(fn(CategorySimple $c) => $c->getId())
                ->values()
                ->toArray();

            $this->parents[$id] = array_unique(
                array_merge([$id], $parentIds)
            );
        }

        return $this->parents[$id];
    }

    /**
     * @param CategorySimple|CategoryModel|int
     * @return CategoryEntityCollection
     * @throws Exception
     */
    public function findParentsChain($category): CategoryEntityCollection
    {
        $id = $this->normalizeCategory($category);

        $parents = CategoryEntityCollection::make();

        if (
            ($current = $this->getAll()->get($id))
            && $current->hasParent()
            && ($parent = $current->getParent())
        ) {
            $parents->put($parent->getId(), $parent);

            $parents = $parents->merge($this->findParentsChain($parent));
        }

        return $parents;
    }

    public function find(Category $category): ?CategorySimple
    {
        return $this->all->get($category->id);
    }

    public function getTree(): CategoryEntityCollection
    {
        return $this->tree;
    }

    /**
     * @param array $categoryIds
     * @return array
     * @throws Exception
     */
    public function findParentIdsForAll(array $categoryIds): array
    {
        $result = [];

        foreach ($categoryIds as $categoryId) {
            $result = array_merge($result, $this->findParentIds($categoryId));
        }

        return array_values(
            array_unique($result)
        );
    }

    /**
     * @param CategorySimple|CategoryModel|int
     * @return array
     * @throws Exception
     */
    public function getAllChildrenIds($category): array
    {
        $category = $this->getAll()->get(
            $id = $this->normalizeCategory($category)
        );

        $result[] = $id;

        if (empty($category)) {
            return $result;
        }

        foreach ($category->getChildren() as $child) {
            $result = array_merge($result, $this->getAllChildrenIds($child));
        }

        return $result;
    }

    /**
     * @param CategorySimple|CategoryModel|int
     * @return array
     * @throws Exception
     */
    public function getFirstLineOfChildren($category): array
    {
        $category = $this->getAll()->get(
            $this->normalizeCategory($category)
        );

        return $category->getChildren()
            ->map(fn(CategorySimple $c) => $c->getId())
            ->toArray();
    }
}
