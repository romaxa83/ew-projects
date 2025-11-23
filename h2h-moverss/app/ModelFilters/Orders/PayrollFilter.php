<?php

namespace App\ModelFilters\Orders;

use App\Enums\Common\DateFormat;
use App\ModelFilters\BaseModelFilter;
use App\Models\Order;
use Carbon\CarbonImmutable;

/**
 * @mixin Order\Payroll\Payroll
 */
class PayrollFilter extends BaseModelFilter
{
    public const TYPE_ALL = 'all';
    public const TYPE_PROCESSED = 'processed';
    public const TYPE_UNPROCESSED = 'unprocessed';

    public function type(string $value): void
    {
        if($value === self::TYPE_ALL) return;

        if($value === self::TYPE_PROCESSED){
            $this->where('is_processed', true);
        }
        if($value === self::TYPE_UNPROCESSED){
            $this->where('is_processed', false);
        }
    }

    public function startRange(string $value): void
    {
        $date = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
            ->startOfDay()
            ->setTimezone('UTC')
        ;

        $this->where('created_at', '>=', $date);
    }

    public function endRange(string $value): void
    {
        $date = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
            ->endOfDay()
            ->setTimezone('UTC')
        ;

        $this->where('created_at', '<=', $date);
    }

    public function date(string $value): void
    {
        $from = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
            ->startOfDay()
            ->setTimezone('UTC')
        ;
        $to = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
            ->endOfDay()
            ->setTimezone('UTC')
        ;

        $this->whereBetween('created_at', [$from, $to]);
    }

    public function employee(string $value): void
    {
        $this->where(function ($query) use ($value) {
            $query->where('creator_id', $value);
        });
    }
}

