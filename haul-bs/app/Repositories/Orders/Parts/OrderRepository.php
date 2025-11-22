<?php

namespace App\Repositories\Orders\Parts;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Orders\Parts\Order;
use App\Taps\Orders\Parts\NotDraft;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class OrderRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Order::class;
    }

    public function getCustomPagination(
        array $filters = [],
    ): LengthAwarePaginator
    {
        return Order::query()
            ->with([
                'customer',
                'salesManager',
                'items',
                'shippingMethods',
                'deliveries',
                'comments',
                'items.inventory.media'
            ])
            ->filter($filters)
            ->tap(new NotDraft())
            ->when(!isset($filters['status']), function ($query) {
                $query->whereNotIn('status', [OrderStatus::Canceled]);
            })
            ->orderbyRaw("
                CASE
                    WHEN status = '". OrderStatus::New() ."' THEN 1
                    WHEN status = '". OrderStatus::In_process() ."' THEN 2
                    WHEN status = '". OrderStatus::Sent() ."' THEN 3
                    WHEN status = '". OrderStatus::Pending_pickup() ."' THEN 4
                    WHEN status = '". OrderStatus::Delivered() ."' THEN 5
                    WHEN status = '". OrderStatus::Returned() ."' THEN 6
                    WHEN status = '". OrderStatus::Lost() ."' THEN 7
                    WHEN status = '". OrderStatus::Damaged() ."' THEN 8
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function getById(int $id): BaseModel|Order
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.parts.not_found"));
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
