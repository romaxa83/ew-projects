<?php

namespace App\ModelFilters\Orders\Parts;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Foundations\Models\BaseModelFilter;
use App\Models\Orders\Parts\Order;
use App\Models\Settings\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** @mixin Order */
class OrderFilter extends BaseModelFilter
{
    public function forSalesManager(int|string $id): void
    {
        $this->where(function (Builder $query) use ($id) {
            return $query->where('sales_manager_id', $id)
                ->orWhereNull('sales_manager_id')
                ;
        });
    }

    public function salesManager(int|string $id): void
    {
        $this->where('sales_manager_id', $id);
    }

    public function search(string $value): void
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->where(function (Builder $query) use ($searchString) {
            return $query->whereRaw('lower(order_number) like ?', [$searchString])
                ;
        });
    }

    public function source(string $value): void
    {
        $this->where('source', $value);
    }

    public function searchInventory(string $value): void
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->whereHas('inventories', function (Builder $query) use ($searchString) {
            return $query->whereRaw('lower(name) like ?', [$searchString])
                ->orWhereRaw('lower(stock_number) like ?', [$searchString])
                ->orWhereRaw('lower(article_number) like ?', [$searchString])
                ;
        });
    }

    public function searchCustomer(string $value): void
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';

        $this->where(function(Builder $q) use ($searchString) {
            $q->whereHas('customer', function (Builder $query) use ($searchString) {
                return $query
                    ->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$searchString])
                    ->orWhereRaw('lower(email) like ?', [$searchString])
                    ;
            })
                ->orWhereRaw('lower(ecommerce_client_email) like ?', [$searchString])
                ->orWhereRaw('lower(ecommerce_client_name) like ?', [$searchString])
            ;
        });
    }

    public function status(string $value): void
    {
        $this->where('status', $value);
    }

    public function paymentStatus(string $value): void
    {
        match ($value){
            OrderPaymentStatus::Paid->value => $this->where('is_paid', true),
            OrderPaymentStatus::Not_paid->value => $this->where('is_paid', false),
            OrderPaymentStatus::Refunded->value => $this->whereNotNull('refunded_at'),
        };
    }

    public function dateFrom(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new CarbonImmutable($date, $timeZone))->startOfDay()->setTimezone('UTC');
        $this->where('created_at', '>=', $dateFrom);
    }

    public function dateTo(string $date): void
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateTo = (new CarbonImmutable($date, $timeZone))->endOfDay()->setTimezone('UTC');
        $this->where('created_at', '<=', $dateTo);
    }
}
