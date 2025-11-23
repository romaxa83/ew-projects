<?php

namespace WezomCms\Catalog\Contracts;

interface ReviewRatingInterface
{
    /**
     * @return int
     */
    public function getRating(): int;
}
