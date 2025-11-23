<?php

namespace App\Models;

use App\Models\Calculation\LocalHourlyRates;
use App\Models\Import\Authorize\Account;
use App\Models\Order\CustomerPage;
use App\Utils\UpdateRelationsTrait;
use Carbon\CarbonImmutable;
use Database\Factories\Divisions\DivisionFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany, Relations\HasOne, SoftDeletes};

/**
 * App\Models\Division
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property array|null $miscs
 * @property string|null $short
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $afterwords_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CustomerPage[] $afterwords
 * @method static \Illuminate\Database\Eloquent\Builder|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Division newQuery()
 * @method static \Illuminate\Database\Query\Builder|Division onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Division withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Division withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereShort($value)
 * @property-read Account|null $authorize
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PaymentAccount[] $paymentAccounts
 * @property-read int|null $payment_accounts_count
 * @method static DivisionFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Division extends Model
{
    use SoftDeletes;
    use UpdateRelationsTrait;
    use HasFactory;

    public const IL_ID = 1;
    public const LA_ID = 2;

    public const TABLE = 'divisions';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'title',
        'short',
        'miscs'
    ];

    protected $casts = [
        'miscs' => 'array',
    ];

    protected static function newFactory(): DivisionFactory
    {
        return DivisionFactory::new();
    }

    public function afterwords(): HasMany
    {
        return $this->hasMany(CustomerPage::class, 'division_id');
    }

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class);
    }

    public function authorize(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function getNowSeason(): string
    {
        if(
            $this->miscs['local_rates_summer_from']
            && $this->miscs['local_rates_summer_to']
            && $this->miscs['tz']
        ){

            $tz = $this->miscs['tz'] ?? 'America/Chicago';

            $date = CarbonImmutable::now($tz);

            $year = $date->format('Y');
            $from = $this->miscs['local_rates_summer_from'];
            $to = $this->miscs['local_rates_summer_to'];

            $summerFrom = CarbonImmutable::createFromFormat('Y-m-d', $year . '-' . $from, $tz)
                ->modify('00:00:00');
            $summerTo = CarbonImmutable::createFromFormat('Y-m-d', $year . '-' . $to, $tz)
                ->modify('23:59:59');

            if ($date >= $summerFrom && $date <= $summerTo)
                return LocalHourlyRates::SEASON_SUMMER;

        }

        return LocalHourlyRates::SEASON_WINTER;
    }
}
