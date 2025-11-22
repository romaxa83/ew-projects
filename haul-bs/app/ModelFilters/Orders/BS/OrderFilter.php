<?php

namespace App\ModelFilters\Orders\BS;

use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Models\BaseModelFilter;
use App\Models\Orders\BS\Order;
use App\Models\Settings\Settings;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** @mixin Order */
class OrderFilter extends BaseModelFilter
{
    public function search(string $value): void
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->where(
            function (Builder $query) use ($searchString) {
                return $query->whereRaw('lower(order_number) like ?', [$searchString])
                    ->orWhereHas('vehicle', function(Builder $query) use ($searchString) {
                        return $query->whereRaw('lower(vin) like ?', [$searchString])
                            ->orWhereRaw('lower(unit_number) like ?', [$searchString])
//                            ->orWhereHas('owner',
//                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
//                            )
                            ->orWhereHas('customer',
                                fn(Builder $query) => $query->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                            );

                    });
            }
        );
    }

    public function status(string $value): void
    {
        if (OrderStatus::isDeletedFromValue($value)) {
            $this->withTrashed();
        }

        $this->where('status', $value);
    }

    public function statuses(array $value): void
    {
        $this->whereIn('status', $value);
    }

    public function vehicleYear(string $value): void
    {
        $this->where(function(Builder $query) use ($value) {
            return $query->whereHas('vehicle',
                fn(Builder $query) => $query->where('year', $value)
            );
        });
    }

    public function vehicleMake(string $value): void
    {
        $this->where(function(Builder $query) use ($value) {
            return $query->whereHas('vehicle',
                fn(Builder $query) => $query->where('make', $value)
            );
        });
    }

    public function vehicleModel(string $value): void
    {
        $this->where(function(Builder $query) use ($value) {
            return $query->whereHas('vehicle',
                fn(Builder $query) => $query->where('model', $value)
            );
        });
    }

    public function paymentStatus(string $value): void
    {
        $now = CarbonImmutable::now();

        match ($value){
            OrderPaymentStatus::Paid->value => $this->where('is_paid', true),
            OrderPaymentStatus::Not_paid->value => $this->where('is_paid', false),
            OrderPaymentStatus::Billed->value => $this->where('is_billed', true)
                ->where('is_paid', false),
            OrderPaymentStatus::Not_billed->value => $this->where('is_billed', false)
                ->where('is_paid', false),
            OrderPaymentStatus::Overdue->value => $this->where('is_paid', false)
                ->where('due_date', '<', $now->startOfDay()),
            OrderPaymentStatus::Not_overdue->value => $this->where('due_date', '>=', $now->startOfDay()),
        };
    }

    public function paymentStatuses(array $value): void
    {
        $now = CarbonImmutable::now();

        $this->where(function (Builder $builder) use ($value, $now) {
            foreach ($value as $item) {
                $builder->orWhere(function (Builder $builder) use ($item, $now) {

                    switch ($item) {
                        case OrderPaymentStatus::Paid->value:
                            $this->where('is_paid', true);
                            break;
                        case OrderPaymentStatus::Not_paid->value:
                            $this->where('is_paid', false);
                            break;
                        case OrderPaymentStatus::Billed->value:
                            $this->where('is_billed', true)
                                ->where('is_paid', false);
                            break;
                        case OrderPaymentStatus::Not_billed->value:
                            $this->where('is_billed', false)
                                ->where('is_paid', false);
                            break;
                        case OrderPaymentStatus::Overdue->value:
                            $this->where('is_paid', false)
                                ->where('due_date', '<', now()->startOfDay());
                            break;
                        case OrderPaymentStatus::Not_overdue->value:
                            $this->where('due_date', '>=', now()->startOfDay());
                            break;
                    }
                });
            }
        });
    }

    public function implementationDateFrom(string $date): void
    {
        $this->dateFrom($date);
    }

    public function implementationDateTo(string $date): void
    {
        $this->dateTo($date);
    }

    public function dateFrom(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new CarbonImmutable($date, $timeZone))->startOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '>=', $dateFrom);
    }

    public function dateTo(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateTo = (new CarbonImmutable($date, $timeZone))->endOfDay()->setTimezone('UTC');
        $this->where('implementation_date', '<=', $dateTo);
    }

    public function inventory(int|string $value): void
    {
        $this->whereHas('inventories',
            fn(Builder $q) => $q->where('inventory_id', $value)
        );
    }

    public function truck(int|string $value): void
    {
        $this->where('vehicle_type', Truck::MORPH_NAME)
            ->where('vehicle_id', $value);
    }

    public function trailer(int|string $value): void
    {
        $this->where('vehicle_type', Trailer::MORPH_NAME)
            ->where('vehicle_id', $value);
    }

    public function mechanic(int|string $value): void
    {
        $this->where('mechanic_id', $value);
    }
}

