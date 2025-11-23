<?php

namespace WezomCms\Catalog\Widgets\Site;

class Novelty extends BaseProductsCarouselByFlag
{
    /**
     * Return product field name for filtering.
     *
     * @return string
     */
    public function flagName(): string
    {
        return 'novelty';
    }

    /**
     * Return widget heading.
     *
     * @return string|null
     */
    public function heading(): ?string
    {
        return __('cms-catalog::site.products.Novelty products');
    }

    /**
     * Return count items
     * @return int
     */
    public function limit()
    {
        return 5;
    }
}
