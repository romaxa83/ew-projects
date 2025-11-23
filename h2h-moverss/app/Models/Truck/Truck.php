<?php

namespace App\Models\Truck;

use App\User;
use App\Utils\UpdateRelationsTrait;
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, SoftDeletes};
use Database\Factories\Trucks\TruckFactory;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Truck
 *
 * @property int $id
 * @property int $active
 * @property array|null $division_ids
 * @property string|null $title
 * @property string|null $nickname
 * @property string|null $vendor
 * @property string|null $model
 * @property string|null $year
 * @property string|null $color
 * @property string|null $l_plate
 * @property string|null $vin
 * @property float|null $length
 * @property float|null $cuft
 * @property float|null $lbs
 * @property int|null $start_mi
 * @property int|null $cur_mi
 * @property string|null $p_color
 * @property string|null $avi_date
 * @property string|null $reg_date
 * @property string|null $tech_date
 * @property int|null partner_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Busy[] $busyDates
 * @property-read int|null $busy_dates_count
 * @property-read BusyWeekDay|null $busyWeeksDays
 * @property-read \Illuminate\Database\Eloquent\Collection|Notes[] $notes
 * @property-read int|null $notes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Truck partnerTrucks()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck active()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck activeDispatch($assignedWorks)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck newQuery()
 * @method static \Illuminate\Database\Query\Builder|Truck onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck query()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck records()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereAviDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereCuft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereCurMi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereLPlate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereLbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck wherePColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereRegDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereStartMi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereTechDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereVendor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereVin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereYear($value)
 * @method static \Illuminate\Database\Query\Builder|Truck withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Truck withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Truck whereDivisionIds($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static Builder|Truck wherePartnerId($value)
 * @mixin \Eloquent
 * @method static TruckFactory factory(...$parameters)
 */

class Truck extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UpdateRelationsTrait;
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'trucks';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'truck';

    protected $fillable = [
        'title',
        'year',
        'active',
        'color',
        'l_plate',
        'model',
        'nickname',
        'p_color',
        'vendor',
        'vin',
        'division_ids',
        'partner_id',
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'division_ids' => 'json',
    ];

    protected static function newFactory(): TruckFactory
    {
        return TruckFactory::new();
    }

    public function busyDates()
    {
        return $this->hasMany(Busy::class);
    }

    public function busyWeeksDays()
    {
        return $this->hasOne(BusyWeekDay::class);
    }

    public function notes()
    {
        return $this->hasMany(Notes::class);
    }

    public function scopeRecords($query)
    {
        return $query
            ->with([
                'notes',
                'busyDates',
                'busyWeeksDays',
            ]);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Данные для выборки машин для диспатча.
     * @param $q
     * @param $assignedWorks
     * @return mixed
     */
    public function scopeActiveDispatch($q, $assignedWorks)
    {
        $ids = $assignedWorks->pluck('dispatchTrucks.*.truck_id')->filter()->flatten()->all();

        return $q
            ->where(function ($q) use ($ids) {
                $q->whereActive(1)
                    ->whereJsonContains('division_ids', request()->session()->get('division.id'));
                if ($ids) {
                    $q->orWhereIn('id', $ids);
                }
            })
            ->select(['id', 'title', 'year', 'p_color', 'active'])
            ->orderBy('title');
    }

    public function scopePartnerTrucks(Builder $builder): Builder
    {
        if(\Auth::user()->isPartner()){
            /** @var $user User */
            $user = \Auth::user();
            if($user->employee->partner_id){
                $builder->where('partner_id', $user->employee->partner_id);
            } else {
                // здесь не нужно возвращать данные, костыльное решение
                $builder->where('created', '<', '1990-01-01');
            }
        }

        return $builder;
    }
}
