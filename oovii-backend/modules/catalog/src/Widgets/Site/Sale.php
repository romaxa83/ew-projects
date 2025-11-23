<?php

namespace WezomCms\Catalog\Widgets\Site;

class Sale extends BaseProductsCarouselByFlag
{
    /**
     * Return product field name for filtering.
     *
     * @return string
     */
    public function flagName(): string
    {
        return 'sale';
    }

    /**
     * Return widget heading.
     *
     * @return string|null
     */
    public function heading(): ?string
    {
        return __('cms-catalog::site.products.Sale products');
    }

    /**
     * Return count items
     * @return int
     */
    public function limit()
    {
        return 10;
    }
}
