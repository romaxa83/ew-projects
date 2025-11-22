<?php

declare(strict_types=1);

namespace App\Traits\Filter;

trait BelongsToCompanyFilterTrait
{
    public function company(int $id): void
    {
        $this->where($this->getTable() . '.company_id', $id);
    }
}
