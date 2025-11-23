<?php

namespace WezomCms\Catalog\Traits;

use Illuminate\Support\Collection as SupportCollection;
use WezomCms\Catalog\Models\Product;

/**
 * Trait ProductFlagsTrait
 * @package WezomCms\Catalog\Traits
 * @mixin Product
 */
trait ProductFlagsTrait
{

    /**
     * @return bool
     */
    public function getHasFlagAttribute(): bool
    {
        return $this->popular || $this->best_price || $this->novelty || $this->sale;
    }

    /**
     * @return string
     */
    public function getFlagColorAttribute(): string
    {
        if ($this->popular) {
            return 'hit';
        }

        if ($this->best_price) {
            return 'best-price';
        }

        if ($this->sale) {
            return 'sale';
        }

        if ($this->novelty) {
            return 'new';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFlagTextAttribute(): string
    {
        if ($this->popular) {
            return __('cms-catalog::site.flags.popular');
        }

        if ($this->best_price) {
            return __('cms-catalog::site.flags.best price');
        }

        if ($this->sale) {
            return __('cms-catalog::site.flags.sale');
        }

        if ($this->novelty) {
            return __('cms-catalog::site.flags.novelty');
        }

        return '';
    }

    /**
     * @return SupportCollection
     */
    public function getFlagsAttribute(): SupportCollection
    {
        $result = collect();

        if ($this->popular) {
            $result->push([
                'name' => 'popular',
                'color' => config('cms.catalog.products.flags.colors.popular'),
                'text' => __('cms-catalog::site.flags.popular'),
            ]);
        }

        if ($this->best_price) {
            $result->push([
                'name' => 'best_price',
                'color' => config('cms.catalog.products.flags.colors.best_price'),
                'text' => __('cms-catalog::site.flags.best price'),
            ]);
        }

        if ($this->cost_discount && ($this->cost_discount < $this->cost)) {
            $result->push([
                'name' => 'sale',
                'color' => config('cms.catalog.products.flags.colors.sale'),
                'text' => $this->getSaleText(),
            ]);
        }

        if ($this->novelty) {
            $result->push([
                'name' => 'novelty',
                'color' => config('cms.catalog.products.flags.colors.novelty'),
                'text' => __('cms-catalog::site.flags.novelty'),
            ]);
        }

        return $result;
    }

    /**
     * @return array|string|null
     */
    private function getSaleText()
    {
        $percent = floor(($this->cost - $this->cost_discount) * 100 / $this->cost);

        return __(
            'cms-catalog::site.catalog.Sale :percent',
            [ 'percent' => $percent ]
        );
    }
}
