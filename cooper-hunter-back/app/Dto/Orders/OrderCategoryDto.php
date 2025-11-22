<?php

namespace App\Dto\Orders;

use App\Dto\BaseDictionaryDto;
use App\Models\Orders\Categories\OrderCategory;

class OrderCategoryDto extends BaseDictionaryDto
{
    protected function getDefaultActive(): bool
    {
        return OrderCategory::DEFAULT_ACTIVE;
    }
}
