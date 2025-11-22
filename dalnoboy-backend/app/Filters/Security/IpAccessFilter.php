<?php

namespace App\Filters\Security;

use App\Filters\BaseModelFilter;
use App\Models\Security\IpAccess;
use App\Traits\Filter\SortFilterTrait;

/**
 * @mixin IpAccess
 */
class IpAccessFilter extends BaseModelFilter
{
    use SortFilterTrait;

    public function query(string $value): void
    {
        $this->where('address', 'like', "%$value%")
            ->orWhereRaw('LOWER(description) LIKE ?', ["%$value%"]);
    }

    public function customAddressSort(string $direction): void
    {
        $this->orderByRaw(sprintf('INET_ATON(%s) %s', 'address', $direction));
    }

    public function customActiveSort(string $direction): void
    {
        $this->orderBy('active', $direction)
            ->orderBy('id');
    }

    protected function allowedOrders(): array
    {
        return IpAccess::ALLOWED_SORTING_FIELDS;
    }
}
