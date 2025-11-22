<?php


namespace App\ModelFilters\Saas\Invoices;


use App\Models\Billing\Invoice;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class InvoiceFilter extends ModelFilter
{
    public function name($value): void
    {
        $this->where(
            Invoice::TABLE_NAME . '.company_name',
            'ILIKE',
            "%{$value}%"
        );
    }

    public function hasGpsSubscription($value): void
    {
        $value = to_bool($value);

        $this->where('has_gps_subscription', $value);
    }

    public function company(int $companyId): void
    {
        $this->where(Invoice::TABLE_NAME . '.carrier_id', $companyId);
    }

    public function paymentStatus(string $value): void
    {
        if ($value === 'paid') {
            $this->where(Invoice::TABLE_NAME . '.is_paid', true);
        } elseif ($value === 'not_paid') {
            $this->where(Invoice::TABLE_NAME . '.is_paid', false);
        }
    }

    public function datesRange(string $datesRange): void
    {
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s*\-\s*(\d{2}\/\d{2}\/\d{4})/', $datesRange, $m)) {
            $start = date('Y-m-d', strtotime($m[1] . ' 00:00:00'));
            $end = date('Y-m-d', strtotime($m[2] . ' 23:59:59'));

            $this->where(
                function (Builder $query) use ($start, $end) {
                    $query->where(
                        function (Builder $q) use ($start) {
                            $q->where(
                                [
                                    [Invoice::TABLE_NAME . '.billing_start', '<=', $start],
                                    [Invoice::TABLE_NAME . '.billing_end', '>=', $start],
                                ]
                            );
                        }
                    )->orWhere(
                        function (Builder $q) use ($end) {
                            $q->where(
                                [
                                    [Invoice::TABLE_NAME . '.billing_start', '<=', $end],
                                    [Invoice::TABLE_NAME . '.billing_end', '>=', $end],
                                ]
                            );
                        }
                    )->orWhere(
                        function (Builder $q) use ($start, $end) {
                            $q->where(
                                [
                                    [Invoice::TABLE_NAME . '.billing_start', '>=', $start],
                                    [Invoice::TABLE_NAME . '.billing_end', '<=', $end],
                                ]
                            );
                        }
                    );
                }
            );
        }
    }

    public function paidDatesRange(string $paidDatesRange): void
    {
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s*\-\s*(\d{2}\/\d{2}\/\d{4})/', $paidDatesRange, $m)) {
            $start = strtotime($m[1] . ' 00:00:00');
            $end = strtotime($m[2] . ' 23:59:59');

            $this->where(
                [
                    [Invoice::TABLE_NAME . '.paid_at', '>=', $start],
                    [Invoice::TABLE_NAME . '.paid_at', '<=', $end],
                ]
            );
        }
    }

    public function isPaid($value): void
    {
        $value = to_bool($value);

        $this->where('is_paid', $value);
    }

    public function attempt($value): self
    {
        if(is_array($value)){
            return $this->whereIn('attempt', $value);
        }

        return $this->where('attempt', $value);
    }
}
