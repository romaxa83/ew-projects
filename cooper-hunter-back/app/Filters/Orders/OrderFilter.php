<?php

namespace App\Filters\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderFilterTabEnum;
use App\Enums\Orders\OrderFilterTrkNumberExistsEnum;
use App\Filters\BaseModelFilter;
use App\Models\Orders\Order;
use App\Traits\Filter\LikeRawFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Class OrderCategoryFilter
 * @package App\Filters\Orders
 *
 * @mixin Order
 */
class OrderFilter extends BaseModelFilter
{

    use LikeRawFilterTrait;

    public function id(int $id): void
    {
        $this->where(Order::TABLE . '.id', $id);
    }

    public function query(string $query): void
    {
        $this->likeRaw("id", $query)
            ->orWhereHas(
                'product',
                fn(Builder $builder) => $this->likeRaw("lower(title)", $query, $builder)
            );
    }

    public function tab(string $tab): void
    {
        if ($tab === OrderFilterTabEnum::ACTIVE) {
            self::active();

            return;
        }

        self::history();
    }

    public function status(array|string $statuses): void
    {
        $this->whereIn('status', Arr::wrap($statuses));
    }

    public function costStatus(array $costStatuses): void
    {
        $this->where(
            function (Builder $builder) use ($costStatuses) {
                foreach ($costStatuses as $status) {
                    $builder->orWhere(
                        fn(Builder|Order $orBuilder) => match ($status) {
                            OrderCostStatusEnum::NOT_FORMED => $orBuilder->costNotFormed(),
                            OrderCostStatusEnum::PAID => $orBuilder->costPaid(),
                            OrderCostStatusEnum::WAITING_TO_PAY => $orBuilder->costWaitingToPay(),
                            OrderCostStatusEnum::REFUND_COMPLETE => $orBuilder->costRefund(),
                        }
                    );
                }
            }
        );
    }

    public function trkNumberExists(string $value): void
    {
        match ($value) {
            OrderFilterTrkNumberExistsEnum::NOT_ASSIGNED => self::trkNumberNotAssigned(),
            OrderFilterTrkNumberExistsEnum::WITH_NUMBER => self::withTrkNumber(),
        };
    }

    public function project(array $projectIds): void
    {
        $this->whereIn('project_id', $projectIds);
    }

    public function technician(string $technicianId): void
    {
        self::whereTechnicianId($technicianId);
    }

    public function technicianName(string $technicianName): void
    {
        $technicianName = '%' . mb_convert_case($technicianName, MB_CASE_LOWER) . '%';

        $this->whereHas(
            'technician',
            fn(Builder $builder) => $builder->whereRaw(
                "lower(concat(first_name, ' ', last_name)) LIKE ?",
                [$technicianName]
            )
        );
    }

    public function dateFrom(string $date): void
    {
        $date = Carbon::parse($date)
            ->startOfDay()
            ->toDateTimeString();

        $this->where('created_at', '>=', $date);
    }

    public function dateTo(string $date): void
    {
        $date = Carbon::parse($date)
            ->endOfDay()
            ->toDateTimeString();

        $this->where('created_at', '<=', $date);
    }

    public function serialNumber(string $serialNumber): void
    {
        self::whereSerialNumber($serialNumber);
    }

    public function recipientName(string $recipientName): void
    {
        $recipientName = '%' . mb_convert_case($recipientName, MB_CASE_LOWER) . '%';

        $this->whereHas(
            'shipping',
            fn(Builder $builder) => $builder->whereRaw(
                "lower(concat(first_name, ' ', last_name)) LIKE ?",
                [$recipientName]
            )
        );
    }
}
