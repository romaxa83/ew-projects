<?php

namespace WezomCms\Catalog\Filter\Handlers;

use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Contracts\TemplateParametersInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\SelectionHandlers\KeywordSearch;
use WezomCms\Catalog\Filter\Sort;
use WezomCms\Catalog\Models\Brand;

class BrandHandler extends AbstractHandler implements SelectedAttributesInterface, TemplateParametersInterface
{
    public const NAME = 'brand';

    /**
     * @return array
     */
    public function supportedParameters(): array
    {
        return [static::NAME];
    }

    /**
     * @return bool
     * @throws IncorrectUrlParameterException
     */
    public function validateParameters(): bool
    {
        $urlBuilder = $this->filter->getUrlBuilder();

        if (!$urlBuilder->has(static::NAME)) {
            return true;
        }

        $slugs = $urlBuilder->get(static::NAME, []);
        if (empty($slugs)) {
            throw new IncorrectUrlParameterException();
        }

        // Check for existence
        if (count($slugs) !== $this->selected()->count()) {
            throw new IncorrectUrlParameterException();
        }

        return true;
    }

    /**
     * @param  Builder  $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $ids = [];
        if (isset($criteria[static::NAME])) {
            $ids = $criteria[static::NAME];
        } elseif ($this->selected()->isNotEmpty()) {
            $ids = $this->selected()->pluck('id')->toArray();
        }

        if (!empty($ids)) {
            $queryBuilder->whereIn('brand_id', $ids);
        }
    }

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable
    {
        $ids = tap($filter->getStorage()->beginSelection(false), function ($query) use ($filter) {
            $filter->applyOnlyHandlers(
                $query,
                [
                    KeywordSearch::class,
                    CategoryWithSubCategoriesHandler::class,
                    CategoryHandler::class,
                ]
            );
        })->select('brand_id')
            ->distinct()
            ->pluck('brand_id')
            ->filter();

        $brands = Brand::published()
            ->without('translation')
            ->whereIn('brands.id', $ids)
            ->orderByTranslation('name')
            ->get();

        if ($brands->isEmpty()) {
            return [];
        }

        $hasResults = $this->hasResults($brands->pluck('id'));

        $options = collect();
        $urlBuilder = $this->filter->getUrlBuilder();
        foreach ($brands as $brand) {
            $selected = $urlBuilder->present(static::NAME, $brand->slug);

            $options->push([
                'name' => $brand->name,
                'value' => $brand->slug,
                'selected' => $selected,
                'disabled' => !$selected && in_array($brand->id, $hasResults) === false,
                'url' => $urlBuilder->autoBuild(static::NAME, $brand->slug),
            ]);
        }

        /**
         * @var $disabled Collection
         * @var $enabled Collection
         */
        list($disabled, $enabled) = $options->partition('disabled');

        return [
            static::NAME => [
                'type' => FilterFormBuilder::TYPE_CHECKBOX,
                'name' => static::NAME,
                'sort' => 2,
                'title' => __('cms-catalog::site.products.Brand'),
                'options' => $enabled->merge($disabled)->values()->all(),
            ],
        ];
    }

    /**
     * @return array|Collection|mixed
     */
    private function selected()
    {
        return Cache::driver('array')->rememberForever(__METHOD__, function () {
            $slugs = $this->filter->getUrlBuilder()->get(static::NAME, []);

            if ($slugs) {
                return Brand::select('id', 'slug')
                    ->published()
                    ->whereIn('slug', $slugs)
                    ->get();
            }

            return collect();
        });
    }

    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable
    {
        $urlBuilder = $this->filter->getUrlBuilder();

        return $this->selected()->map(function ($item) use ($urlBuilder) {
            return [
                'group' => static::NAME,
                'name' => $item->name,
                'removeUrl' => $urlBuilder->buildUrlWithout(static::NAME, $item->slug),
            ];
        });
    }

    /**
     * Return array of all supported parameters
     *
     * @return array
     */
    public static function availableParameters(): iterable
    {
        return [static::NAME => __('cms-catalog::admin.brands.Brand') . ' - [' . static::NAME . ']'];
    }

    /**
     * @param  Collection  $ids
     * @return array
     */
    protected function hasResults(Collection $ids): array
    {
        return tap($this->filter->getStorage()->beginSelection(false), function ($query) {
            $this->filter->applyHandlers($query, [static::class, Sort::class]);
        })->select('brand_id')
            ->whereIn('brand_id', $ids)
            ->distinct()
            ->pluck('brand_id')
            ->filter()
            ->all();
    }
}
