<?php

namespace WezomCms\Catalog\Filter\Handlers;

use Illuminate\Database\Query\Builder;
use WezomCms\Catalog\Filter\AttachedCategory;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\HasAnAttachedCategoryInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Helpers;

class CategoryWithSubCategoriesHandler extends CategoryHandler implements
    HasAnAttachedCategoryInterface,
    SelectedAttributesInterface
{
    use AttachedCategory;

    /**
     * @var array|null
     */
    private $ids;

    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable
    {
        $query = app('request')->except('category');

        return [
            [
                'group' => 'category',
                'name' => $this->category->name,
                'removeUrl' => url()->current() . ($query ? '?' . http_build_query($query) : ''),
            ]
        ];
    }

    /**
     * @param  Builder  $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     * @throws \Exception
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        if (!$this->category) {
            throw new IncorrectUrlParameterException();
        }

        $queryBuilder->whereIn('category_id', $this->getIds());
    }

    /**
     * @return array|null
     */
    private function getIds(): array
    {
        if (null === $this->ids) {
            if ($this->category->children()->published()->exists()) {
                $this->ids = Helpers::getAllChildes(
                    Category::select('id', 'parent_id')->published()->get(),
                    $this->category->id
                );
            } else {
                $this->ids = [];
            }

            $this->ids[] = $this->category->id;
        }

        return $this->ids;
    }
}
