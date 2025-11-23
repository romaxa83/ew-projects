<?php

namespace WezomCms\Catalog\Filter\Handlers;

use Cache;
use Illuminate\Support\Collection;
use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Contracts\TemplateParametersInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\SelectionHandlers\KeywordSearch;
use WezomCms\Catalog\Models\Model;

class ModelHandler extends AbstractHandler implements SelectedAttributesInterface, TemplateParametersInterface
{
    public const NAME = 'model';

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
     * @param $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $urlBuilder = $filter->getUrlBuilder();

        $ids = [];
        if (isset($criteria[static::NAME])) {
            $ids = $criteria[static::NAME];
        } elseif ($urlBuilder->has(static::NAME)) {
            $ids = $this->selected()->pluck('id')->toArray();
        }

        if (!empty($ids)) {
            $queryBuilder->whereIn('model_id', $ids);
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
                    BrandHandler::class,
                    CategoryWithSubCategoriesHandler::class,
                    CategoryHandler::class,
                ]
            );
        })->select('model_id')
            ->distinct()
            ->pluck('model_id')
            ->filter();

        $models = Model::published()
            ->whereIn('models.id', $ids)
            ->orderByTranslation('name')
            ->get();

        if ($models->isEmpty()) {
            return [];
        }

        $hasResults = $this->hasResults($models->pluck('id'));

        $options = [];
        $urlBuilder = $this->filter->getUrlBuilder();
        foreach ($models as $model) {
            $selected = $urlBuilder->present(static::NAME, $model->slug);

            $options[] = [
                'name' => $model->name,
                'value' => $model->slug,
                'selected' => $selected,
                'disabled' => !$selected && in_array($model->id, $hasResults) === false,
                'url' => $urlBuilder->autoBuild(static::NAME, $model->slug),
            ];
        }

        /**
         * @var $disabled Collection
         * @var $enabled Collection
         */
        list($disabled, $enabled) = collect($options)->partition('disabled');

        return [
            'model' => [
                'type' => FilterFormBuilder::TYPE_CHECKBOX,
                'name' => static::NAME,
                'sort' => 3,
                'title' => __('cms-catalog::site.products.Model'),
                'options' => $enabled->merge($disabled)->toArray(),
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
                return Model::select('id', 'slug')
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
        return [static::NAME => __('cms-catalog::admin.models.Model') . ' - [' . static::NAME . ']'];
    }

    /**
     * @param  Collection  $ids
     * @return array
     */
    private function hasResults(Collection $ids): array
    {
        return tap($this->filter->getStorage()->beginSelection(false), function ($query) {
            $this->filter->applyHandlers($query, [static::class]);
        })->select('model_id')
            ->whereIn('model_id', $ids)
            ->distinct()
            ->pluck('model_id')
            ->filter()
            ->all();
    }
}
