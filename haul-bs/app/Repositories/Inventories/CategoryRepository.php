<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Category;
use App\Foundations\Models\BaseModel;

final readonly class CategoryRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    public function getById(int $id): BaseModel|Category
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.category.not_found"));
    }

    public function lisAsTreeForSelect(): array
    {
        $collection = $this->getList(
            select: ['id', 'name', 'parent_id', 'position']
        );

        $categoryTree = $this->buildTree($collection);
        return $this->flattenTree($categoryTree);
    }

    public function lisAsTree(): array
    {
        $collection = $this->getList(
            select: ['id', 'name', 'slug', 'parent_id', 'position']
        );

        return $this->buildTree($collection);
    }

    function buildTree($categories, $parentId = 0) {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->children = $this->buildTree($categories, $category->id);
                $tree[] = $category;
            }
        }

        usort($tree, function($a, $b) {
            return $a->position <=> $b->position;
        });

        return $tree;
    }

    protected function flattenTree($categories, $prefix = ''): array
    {
        $result = [];
        foreach ($categories as $category) {
            $result[$category['id']] = $prefix . $category['name'];
            if(!empty($category['children'])) {
                $result += $this->flattenTree($category['children'], $prefix . chr(0xC2).chr(0xA0).chr(0xC2).chr(0xA0));
            }
        }

        return $result;
    }
}
