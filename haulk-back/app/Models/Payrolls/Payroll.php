<?php

namespace App\Models\Payrolls;

use App\ModelFilters\Payrolls\PayrollFilter;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Carbon\CarbonImmutable;
use Database\Factories\GPS\AlertFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $send_pdf_at
 *
 * @see static::orders()
 * @property  Order[]|BelongsToMany orders
 */

class Payroll extends Model
{
    use HasFactory;
    use SetCompanyId;
    use Filterable;

    public const TABLE_NAME = 'payrolls';

    protected $fillable = [
        'driver_id',
        'driver_rate',
        'total',
        'subtotal',
        'commission',
        'salary',
        'expenses_before',
        'expenses_after',
        'bonuses',
        'notes',
        'start',
        'end',
        'send_pdf_at',
    ];

    protected $casts = [
        'expenses_before' => 'array',
        'expenses_after' => 'array',
        'bonuses' => 'array',
        'is_paid' => 'boolean',
    ];

    protected $dates = [
        'start',
        'end',
        'send_pdf_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function driver(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'driver_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function modelFilter()
    {
        return $this->provideFilter(PayrollFilter::class);
    }

    public function getStateNamesArr(): array
    {
        $names = [];
        $stateIDs = [];

        $this->orders->each(
            function ($el) use (&$stateIDs) {
                if (isset($el->pickup_contact['state_id'])) {
                    $stateIDs[] = $el->pickup_contact['state_id'];
                }

                if (isset($el->delivery_contact['state_id'])) {
                    $stateIDs[] = $el->delivery_contact['state_id'];
                }
            }
        );

        if (count($stateIDs)) {
            State::select(['id', 'state_short_name'])
                ->findMany($stateIDs)
                ->each(function ($el) use (&$names) {
                    $names[$el->id] = $el->state_short_name;
                });
        }

        return $names;
    }
}
