<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Transaction;

final readonly class TransactionRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Transaction::class;
    }

    public function getCustomPagination(array $filters = [])
    {
        return Transaction::query()
            ->filter($filters)
            ->select('*')
            ->selectPriceWithTaxAndDiscount()
            ->where('is_reserve', false)
            ->orderBy('transaction_date', 'desc')
//            ->orderBy('id', 'desc')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function getReservedCustomPagination(array $filters = [])
    {
        return Transaction::query()
            ->filter($filters)
            ->where('is_reserve', true)
            ->orderBy('transaction_date', 'desc')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function getForReport(array $filters = [])
    {
        return Transaction::query()
            ->select('*')
            ->selectPriceWithTaxAndDiscount()
            ->filter($filters)
            ->where('is_reserve', false)
            ->orderByRaw('transaction_date desc, id desc')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }
}

