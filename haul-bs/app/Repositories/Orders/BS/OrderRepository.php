<?php

namespace App\Repositories\Orders\BS;

use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Orders\BS\Order;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class OrderRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Order::class;
    }

    public function getById(int $id): BaseModel|Order
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"));
    }

    public function customPagination($filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->with(['vehicle.customer', 'comments', 'mechanic'])
            ->select('*')
            ->filter($filters)
            ->orderByDefault()
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function customReportPagination($filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->select('*')
            ->with(['vehicle.customer'])
            ->filter($filters)
            ->orderForReport(
                $filters['order_by'] ?? 'implementation_date',
                $filters['order_type'] ?? 'desc'
            )
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function orderTotalData($filters = []): Order
    {
        return Order::query()
            ->selectRaw('
                SUM(profit) as total_profit,
                SUM(total_amount) as total_amount,
                SUM(debt_amount) as total_due,
                SUM(CASE WHEN due_date >= now() THEN debt_amount ELSE 0 END) as current_due,
                SUM(CASE WHEN due_date < now() THEN debt_amount ELSE 0 END) as past_due
            ')
            ->filter($filters)
            ->first();
    }

    public function getLastForOrderNumber(): ?Order
    {
        return Order::query()
            ->withTrashed()
            ->select('order_number')
            ->where('created_at', '>=', CarbonImmutable::now()->startOfDay())
            ->orderBy('id', 'desc')
            ->first();
    }
}
