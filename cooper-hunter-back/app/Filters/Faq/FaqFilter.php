<?php

namespace App\Filters\Faq;

use App\Filters\BaseModelFilter;
use App\Models\Faq\Faq;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin Faq
 */
class FaqFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function active(bool $active): void
    {
        $this->where('active', $active);
    }
}
