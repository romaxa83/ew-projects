<?php

namespace App\ModelFilters\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Settings\Settings;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class OrderFilter
 *
 * @mixin Order
 *
 * @package App\ModelFilters\BodyShop\Orders
 */
class OrderFilter extends ModelFilter
{
    public function q(string $name): void
    {
        $searchString = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';
        $this->where(
            function (Builder $query) use ($searchString) {
                return $query->whereRaw('lower(order_number) like ?', [$searchString])
                    ->orWhereHas('truck', function(Builder $query) use ($searchString) {
                        return $query->whereRaw('lower(vin) like ?', [$searchString])
                            ->orWhereRaw('lower(unit_number) like ?', [$searchString])
                            ->orWhereHas(
                                'owner',
                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                            )
                            ->orWhereHas(
                                'customer',
                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                            );

                    })
                    ->orWhereHas('trailer', function(Builder $query) use ($searchString) {
                        return $query->whereRaw('lower(vin) like ?', [$searchString])
                            ->orWhereRaw('lower(unit_number) like ?', [$searchString])
                            ->orWhereHas(
                                'owner',
                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                            )
                            ->orWhereHas(
                                'customer',
                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                            );
                    });
            }
        );
    }

    public function mechanic(int $mechanicId): void
    {
        $this->where('mechanic_id', $mechanicId);
    }

    public function status(string $status): void
    {
        if ($status === Order::STATUS_DELETED) {
            $this->withTrashed();
        }

        $this->where('status', $status);
    }

    public function vehicleMake(string $make): void
    {
        $this->where(function(Builder $query) use ($make) {
            return $query->whereHas(
                'truck',
                fn(Builder $query) => $query->where('make', $make)
            )
                ->orWhereHas(
                    'trailer',
                    fn(Builder $query) => $query->where('make', $make)
                );
        });
    }

    public function vehicleModel(string $model): void
    {
        $this->where(function(Builder $query) use ($model) {
            return $query->whereHas(
                'truck',
                fn(Builder $query) => $query->where('model', $model)
            )
                ->orWhereHas(
                    'trailer',
                    fn(Builder $query) => $query->where('model', $model)
                );
        });
    }

    public function vehicleYear(string $year): void
    {
        $this->where(function(Builder $query) use ($year) {
            return $query->whereHas(
                'truck',
                fn(Builder $query) => $query->where('year', $year)
            )
                ->orWhereHas(
                    'trailer',
                    fn(Builder $query) => $query->where('year', $year)
                );
        });
    }

    public function implementationDate(string $date): void
    {
        $dateFrom = Carbon::createFromTimestamp(strtotime($date))->startOfDay();
        $dateTo = Carbon::createFromTimestamp(strtotime($date))->endOfDay();
        $this->whereBetween('implementation_date', [$dateFrom, $dateTo]);
    }

    public function paymentStatus(string $paymentStatus): void
    {
        switch ($paymentStatus) {
            case Order::PAYMENT_STATUS_PAID:
                $this->where('is_paid', true);
                break;
            case Order::PAYMENT_STATUS_NOT_PAID:
                $this->where('is_paid', false);
                break;
            case Order::PAYMENT_STATUS_BILLED:
                $this->where('is_billed', true)
                    ->where('is_paid', false);
                break;
            case Order::PAYMENT_STATUS_NOT_BILLED:
                $this->where('is_billed', false)
                    ->where('is_paid', false);
                break;
            case Order::PAYMENT_STATUS_OVERDUE:
                $this->where('is_paid', false)
                    ->where('due_date', '<', now()->startOfDay());
                break;
            case Order::PAYMENT_STATUS_NOT_OVERDUE:
                $this->where('due_date', '>=', now()->startOfDay());
                break;
        }
    }

    public function statuses(array $statuses): void
    {
        $this->whereIn('status', $statuses);
    }

    public function paymentStatuses(array $paymentStatuses): void
    {
        $this->where(function (Builder $builder) use ($paymentStatuses) {
            foreach ($paymentStatuses as $paymentStatus) {
                $builder->orWhere(function (Builder $builder) use ($paymentStatus) {
                    switch ($paymentStatus) {
                        case Order::PAYMENT_STATUS_PAID:
                            $this->where('is_paid', true);
                            break;
                        case Order::PAYMENT_STATUS_NOT_PAID:
                            $this->where('is_paid', false);
                            break;
                        case Order::PAYMENT_STATUS_BILLED:
                            $this->where('is_billed', true)
                                ->where('is_paid', false);
                            break;
                        case Order::PAYMENT_STATUS_NOT_BILLED:
                            $this->where('is_billed', false)
                                ->where('is_paid', false);
                            break;
                        case Order::PAYMENT_STATUS_OVERDUE:
                            $this->where('is_paid', false)
                                ->where('due_date', '<', now()->startOfDay());
                            break;
                        case Order::PAYMENT_STATUS_NOT_OVERDUE:
                            $this->where('due_date', '>=', now()->startOfDay());
                            break;
                    }
                });
            }
        });
    }

    public function implementationDateFrom(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new Carbon($date, $timeZone))->startOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '>=', $dateFrom);
    }

    public function implementationDateTo(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateTo = (new Carbon($date, $timeZone))->endOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '<=', $dateTo);
    }

    public function dateFrom(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new Carbon($date, $timeZone))->startOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '>=', $dateFrom);
    }

    public function dateTo(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateTo = (new Carbon($date, $timeZone))->endOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '<=', $dateTo);
    }

    public function inventory(int $inventoryId): void
    {
        $this->whereHas(
            'inventories',
            fn(Builder $q) => $q->where('inventory_id', $inventoryId)
        );
    }

    public function truck(int $truckId): void
    {
        $this->whereHas(
            'truck',
            fn(Builder $q) => $q->where('truck_id', $truckId)
        );
    }

    public function trailer(int $trailerId): void
    {
        $this->whereHas(
            'trailer',
            fn(Builder $q) => $q->where('trailer_id', $trailerId)
        );
    }
}
