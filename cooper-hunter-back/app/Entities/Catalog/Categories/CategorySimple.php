<?php

namespace App\Entities\Catalog\Categories;

use App\Models\Catalog\Categories\Category as CategoryModel;
use Illuminate\Support\Collection;
use stdClass;

class CategorySimple
{
    public int $id;

    public ?int $parentId;

    public ?self $parent = null;

    public int $productCount = 0;

    /**
     * @var Collection<CategorySimple>
     */
    public Collection $children;

    public function __construct(CategoryModel|stdClass $category)
    {
        $this->id = $category->id;
        $this->parentId = $category->parent_id;

        $this->children = collect();
    }

    public function hasParent(): bool
    {
        return (bool)$this->parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function hasParentId(): bool
    {
        return (bool)$this->parentId;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function addChild(self $category): self
    {
        $this->children->put($category->getId(), $category);

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CategorySimple[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setProductCount(int $count): self
    {
        $this->productCount = $count;

        return $this;
    }

    public function getTotalCount(): int
    {
        return $this->getProductsCount() + $this->getChildCount();
    }

    public function getProductsCount(): int
    {
        return $this->productCount;
    }

    protected function getChildCount(): int
    {
        return $this->children->sum(fn(CategorySimple $c) => $c->getTotalCount());
    }
}
