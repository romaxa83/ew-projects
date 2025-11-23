<?php

namespace WezomCms\Catalog\Filter\Handlers;

use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Contracts\TemplateParametersInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectSortException;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\Sort;

class CostHandler extends AbstractHandler implements SelectedAttributesInterface, TemplateParametersInterface
{
    public const NAME = 'cost';
    public const NAME_FROM = 'cost_from';
    public const NAME_TO = 'cost_to';

    /**
     * @return array
     */
    public function supportedParameters(): array
    {
        return [
            static::NAME_FROM,
            static::NAME_TO,
        ];
    }

    /**
     * @return bool
     * @throws IncorrectSortException
     * @throws IncorrectUrlParameterException
     */
    public function validateParameters(): bool
    {
        $urlBuilder = $this->filter->getUrlBuilder();

        $from = floor($urlBuilder->first(static::NAME_FROM));
        $to = ceil($urlBuilder->first(static::NAME_TO));

        if ($from < 0 || $to < 0) {
            throw new IncorrectUrlParameterException();
        }

        if ($from && $to && $from > $to) {
            throw (new IncorrectSortException())
                ->setCorrectSort([static::NAME_FROM => [$to], static::NAME_TO => [$from]]);
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

        if ($urlBuilder->has(static::NAME_FROM)) {
            $from = floor($urlBuilder->first(static::NAME_FROM));
            if ($from) {
                $queryBuilder->where('cost', '>=', $from);
            }
        }

        if ($urlBuilder->has(static::NAME_TO)) {
            $to = ceil($urlBuilder->first(static::NAME_TO));
            if ($to) {
                $queryBuilder->where('cost', '<=', $to);
            }
        }
    }

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable
    {
        $range = $filter->getMinMaxFor('cost', [static::class, Sort::class]);

        $min = floor($range ? max(0, $range->min_value) : 0);
        $max = ceil($range ? min(999999999, $range->max_value) : 999999999);

        return [
            [
                'range' => $range,
                'type' => FilterFormBuilder::TYPE_NUMBER_RANGE,
                'sort' => 7,
                'from' => [
                    'name' => static::NAME_FROM,
                    'min' => $min,
                    'value' => $filter->getUrlBuilder()->first(static::NAME_FROM),
                    'placeholder' => ucfirst(__('cms-catalog::site.products.from') . ':'),
                ],
                'to' => [
                    'name' => static::NAME_TO,
                    'max' => $max,
                    'value' => $filter->getUrlBuilder()->first(static::NAME_TO),
                    'placeholder' => ucfirst(__('cms-catalog::site.products.to') . ':'),
                ],
                'title' => __('cms-catalog::site.products.Cost'),
                'currency' => money()->siteCurrencySymbol(),
            ],
        ];
    }

    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable
    {
        $urlBuilder = $this->filter->getUrlBuilder();

        $from = $urlBuilder->first(static::NAME_FROM);
        $to = $urlBuilder->first(static::NAME_TO);

        $currency = money()->siteCurrencySymbol();

        if ($from && $to) {
            return [
                [
                    'group' => static::NAME,
                    'name' => "{$from} - {$to} {$currency}",
                    'removeUrl' => $urlBuilder->buildUrlWithout([static::NAME_FROM => $from, static::NAME_TO => $to]),
                ]
            ];
        } elseif ($from) {
            return [
                [
                    'group' => static::NAME,
                    'name' => sprintf('%s %d %s', __('cms-catalog::site.products.from'), $from, $currency),
                    'removeUrl' => $urlBuilder->buildUrlWithout(static::NAME_FROM, $from),
                ]
            ];
        } elseif ($to) {
            return [
                [
                    'group' => static::NAME,
                    'name' => sprintf('%s %d %s', __('cms-catalog::site.products.to'), $to, $currency),
                    'removeUrl' => $urlBuilder->buildUrlWithout(static::NAME_TO, $to),
                ]
            ];
        } else {
            return [];
        }
    }

    /**
     * Return array of all supported parameters
     *
     * @return array
     */
    public static function availableParameters(): iterable
    {
        return [static::NAME => __('cms-catalog::admin.products.Cost') . ' - [' . static::NAME . ']'];
    }
}
