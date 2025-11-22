<?php

namespace App\Filters\Warranty\Deleted;

use App\Filters\BaseModelFilter;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Traits\Filter\IdFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin WarrantyRegistrationDeleted
 */
class WarrantyRegistrationDeletedFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function memberName(string $name): void
    {
        $name = mb_strtolower($name);

        $this->where(
            static fn(Builder $b) => $b->whereHasMorph(
                'member',
                ['*'],
                static fn(Builder $morph) => $morph
                    ->filter(['query' => $name])
            )
                ->orWhereRaw('LOWER(user_info->"$.first_name") like ?', "%$name%")
                ->orWhereRaw('LOWER(user_info->"$.last_name") like ?', "%$name%")
        );
    }

    public function warrantyStatus(string $status): void
    {
        $this->where('warranty_status', $status);
    }
}

