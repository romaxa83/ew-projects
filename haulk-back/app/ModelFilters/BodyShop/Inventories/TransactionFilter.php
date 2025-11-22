<?php

namespace App\ModelFilters\BodyShop\Inventories;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TransactionFilter extends ModelFilter
{
    public function q(string $name): void
    {
        $searchString = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';

        $this->whereHas(
            'inventory',
            function (Builder $query) use ($searchString) {
                $query->whereRaw('lower(name) like ?', [$searchString])
                    ->orWhereRaw('lower(stock_number) like ?', [$searchString]);
            }
        );
    }

    public function transactionType(string $transactionType): void
    {
        $this->where('operation_type', $transactionType);
    }

    public function category(int $categoryId): void
    {
        $this->whereHas(
            'inventory',
            fn(Builder $q) => $q->where('category_id', $categoryId)
        );
    }

    public function supplier(int $supplierId): void
    {
        $this->whereHas(
            'inventory',
            fn(Builder $q) => $q->where('supplier_id', $supplierId)
        );
    }

    public function dateFrom(string $date): void
    {
        $dateFrom = Carbon::createFromTimestamp(strtotime($date))->startOfDay();
        $this->whereDate('transaction_date', '>=', $dateFrom);
    }

    public function dateTo(string $date): void
    {
        $dateTo = Carbon::createFromTimestamp(strtotime($date))->endOfDay();
        $this->whereDate('transaction_date', '<=', $dateTo);
    }
}
