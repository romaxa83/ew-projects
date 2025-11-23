<?php

namespace WezomCms\Catalog\Filter\Handlers;

use WezomCms\Catalog\Filter\AttachedCategory;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\HasAnAttachedCategoryInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;

class CategoryHandler extends AbstractHandler implements HasAnAttachedCategoryInterface
{
    use AttachedCategory;

    /**
     * Return array of all supported keys.
     *
     * @return array
     */
    public function supportedParameters(): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function validateParameters(): bool
    {
        return true;
    }

    /**
     * @param $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     * @throws \Exception
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        if (!$this->category) {
            throw new IncorrectUrlParameterException();
        }

        $queryBuilder->where('category_id', $this->category->id);
    }

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable
    {
        return [];
    }
}
