<?php

namespace WezomCms\Users\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use WezomCms\Cars\Models\Brand;
use WezomCms\Cars\Models\EngineType;
use WezomCms\Cars\Models\Model;
use WezomCms\Cars\Models\Transmission;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Users\Types\UserCarStatus;
use WezomCms\Users\UseCase\CarStatuses;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $vin_code
 * @property string|null $number
 * @property string|null $number_for_1c
 * @property string|null $year
 * @property string|null $engine_volume
 * @property bool $is_family_car
 * @property bool $is_verify
 * @property int|null $dealership_id
 * @property int|null $brand_id
 * @property int|null $model_id
 * @property int|null $transmission_id
 * @property int|null $engine_type_id
 * @property int $status
 * @property int $niko_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Car active()
 */
class Car extends EloquentModel
{
    use Filterable;

    // дефолтное значение машин в гараже
    const DEFAULT_PERMISSIBLE_COUNT = 5;

    protected $table = 'user_cars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vin_code',
        'number',
        'year',
        'is_family_car',
        'is_verify',
        'engine_volume',
        'dealership_id',
        'brand_id',
        'model_id',
        'transmission_id',
        'engine_type_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_family_car' => 'bool',
        'is_verify' => 'bool'
    ];

    public function isFamilyCar()
    {
        return $this->is_family_car;
    }

    public function isVerify()
    {
        return $this->is_verify;
    }

    public function getDescriptionAttribute()
    {
        $name = '';
        $name .= isset($this->brand->name) ? $this->brand->name : '';
        $name .= isset($this->model->name) ? ',' . $this->model->name : '';
        $name .= isset($this->transmission->name) ? ',' . $this->transmission->name : '';
        $name .= isset($this->engineType->name) ? ',' . $this->engineType->name : '';
        $name .= isset($this->engine_volume) ? ',' . $this->engine_volume : '';
        $name .= isset($this->year) ? ',' . $this->year : '';

        return $name;
    }

    // рендер статусов в админке (в виде бейджев)
    public function getStatusesAttribute()
    {
        $status = new CarStatuses($this);

        return $status->forAdmin();
    }

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(Model::class);
    }

    public function transmission()
    {
        return $this->belongsTo(Transmission::class);
    }

    public function engineType()
    {
        return $this->belongsTo(EngineType::class);
    }

    // scope
    /**
     * @param  Builder  $query
     */
    public function scopeActive(Builder $query)
    {
        $query->where('status', UserCarStatus::ACTIVE);
    }
}

