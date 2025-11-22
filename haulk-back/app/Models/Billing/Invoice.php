<?php

namespace App\Models\Billing;

use App\ModelFilters\Saas\Invoices\InvoiceFilter;
use App\Models\Saas\Company\Company;
use App\Scopes\CompanyScope;
use App\Traits\Filterable;
use Database\Factories\Billing\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property bool has_gps_subscription
 * @property array gps_device_data
 * @property array gps_device_payment_data
 * @property double gps_device_amount
 * @property double drivers_amount
 * @property int count_send_not_paid // кол-во уведомлений об не уплате, после трех не удачных попыток
 *
 * @see static::company()
 * @property Company|null company
 *
 * @method static InvoiceFactory factory(...$parameters)
 */
class Invoice extends Model
{
    use HasFactory;
    use Filterable;

    public const TABLE_NAME = 'invoices';

    protected $fillable = [
        'carrier_id',
        'company_name',
        'count_send_not_paid'
    ];

    protected $casts = [
        'billing_data' => 'array',
        'gps_device_data' => 'array',
        'gps_device_payment_data' => 'array',
        'has_gps_subscription' => 'boolean',
        'gps_device_amount' => 'double',
        'attempt_history' => 'array',
        'pending' => 'boolean',
        'is_paid' => 'boolean',
        'amount' => 'double',
        'drivers_amount' => 'double',
        'billing_start' => 'date',
        'billing_end' => 'date'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class,'carrier_id','id');
    }

    public function modelFilter(): string
    {
        return InvoiceFilter::class;
    }

    public function getCompanyId(): int
    {
        return $this->carrier_id;
    }

    public function chargeAttemptsExhausted(): bool
    {
        return $this->attempt >= config('billing.invoices.max_charge_attempts');
    }

    public function preChargeAttemptsExhausted(): bool
    {
        return $this->attempt == config('billing.invoices.max_charge_attempts') - 1;
    }

    public function formatGpsDeviceDataForPdf(): Collection
    {
        $collection = collect();
        if(!empty($this->gps_device_data)){
            $collection = collect($this->gps_device_data)
                ->sortByDesc('days')
                ->groupBy('days')
                ->map(function (Collection $group){
                    return [
                        'amount' => $group->sum('amount'),
                        'count' => $group->count(),
                        'days' => $group->first()['days'],
                    ];
                })
                ->values()
            ;
        }

        return $collection;
    }

    public function formatDriverDataForPdf(): array
    {
        $tmp = [];
        if(!empty($this->billing_data)){
            foreach($this->billing_data as $key => $data){
                if($key == 0){
                    $tmp[$key] = [
                        'start_period' => $data['date'],
                        'end_period' => $data['date'],
                        'amount' => $data['amount'],
                        'driver_count' => $data['driver_count'],
                    ];
                } else {
                    end($tmp);
                    $lastKey = key($tmp);
                    if($tmp[$lastKey]['driver_count'] == $data['driver_count']){
                        $tmp[$lastKey]['end_period'] = $data['date'];
                        $tmp[$lastKey]['amount'] += $data['amount'];
                    } else {
                        $tmp[$key] = [
                            'start_period' => $data['date'],
                            'end_period' => $data['date'],
                            'amount' => $data['amount'],
                            'driver_count' => $data['driver_count'],
                        ];
                    }
                }
            }
        }

        return $tmp;
    }
}
