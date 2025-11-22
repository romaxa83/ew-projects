<?php

namespace App\ModelFilters\Inventories;

use App\Foundations\Models\BaseModelFilter;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class TransactionFilter extends BaseModelFilter
{
    public function inventory(string|int $value): void
    {
        $this->where('inventory_id', $value);
    }

    public function operationType(string $value): void
    {
        $this->where('operation_type', $value);
    }

    public function transactionType(string $value): void
    {
        $this->where('operation_type', $value);
    }

    public function search(string $value): void
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->whereHas('inventory',
            function (Builder $query) use ($searchString) {
                $query->whereRaw('lower(name) like ?', [$searchString])
                    ->orWhereRaw('lower(stock_number) like ?', [$searchString]);
            }
        );
    }

    public function category(int|string $value): void
    {
        $this->whereHas('inventory',
            fn(Builder $q) => $q->where('category_id', $value)
        );
    }

    public function supplier(int|string $value): void
    {
        $this->whereHas('inventory',
            fn(Builder $q) => $q->where('supplier_id', $value)
        );
    }

    public function dateFrom(string $value): void
    {
        $dateFrom = CarbonImmutable::createFromTimestamp(strtotime($value))->startOfDay();
        $this->whereDate('transaction_date', '>=', $dateFrom);
    }

    public function dateTo(string $value): void
    {
        $dateTo = CarbonImmutable::createFromTimestamp(strtotime($value))->endOfDay();
        $this->whereDate('transaction_date', '<=', $dateTo);
    }
}
