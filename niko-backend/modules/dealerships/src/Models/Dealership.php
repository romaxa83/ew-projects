<?php

namespace WezomCms\Dealerships\Models;

use DebugBar\DataFormatter\DataFormatter;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Dealerships\Traits\RateCountTrait;
use WezomCms\Regions\Models\City;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Types\OrderStatus;

/**
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property string|null $email
 * @property array|null $phones
 * @property string|null $site_link
 * @property integer $city_id
 * @property integer $brand_id
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point|null $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @method static Builder|self countsRate()
 * @mixin DealershipTranslation
 */
class Dealership extends Model
{
    use SpatialTrait;
    use Translatable;
    use PublishedTrait;
    use RateCountTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealerships';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published',
        'city_id',
        'brand_id',
        'location',
        'phones',
        'email',
        'site_link',
    ];

    protected $spatialFields = [
        'location',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published' => 'bool',
        'phones' => 'array',
    ];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = [
        'name',
        'text',
        'address',
        'services',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    public function getPhonesWithDesc()
    {
        $local = \App::getLocale();
        $data = [];
        foreach ($this->phones ?? [] as $key => $phone){
            $data[$key]['phone'] = $phone['phone'];
            $data[$key]['desc'] = $local == 'uk' ? $phone['desc_uk'] : $phone['desc_ru'] ;
        }

        return $data;
    }

    // relations
    public function schedule()
    {
        return $this->hasMany(Schedule::class);
    }

    public function scheduleSalon()
    {
        return $this->schedule()->where('type',  Schedule::TYPE_SALON);
    }

    public function scheduleService()
    {
        return $this->schedule()->where('type',  Schedule::TYPE_SERVICE);
    }

    public function gallery()
    {
        return $this->hasMany(DealershipImages::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getScheduleSalonDay($day)
    {
        return $this->scheduleSalon->where('day', $day)->first();
    }

    public function getScheduleServiceDay($day)
    {
        return $this->scheduleService->where('day', $day)->first();
    }

    public function orders()
    {
        return $this->hasMany(ServicesOrder::class);
    }

    public function ordersDone()
    {
        return $this->orders()->where('status', OrderStatus::DONE);
    }

    public function getScheduleForFront($type)
    {
        $data = [];
        foreach (Schedule::daysForSchedule() as $day){
            $model = $type === Schedule::TYPE_SERVICE
                ? $this->getScheduleServiceDay($day)
                : $this->getScheduleSalonDay($day);
            $data[$day] = [];
            if(isset($model->work_start)){
                $data[$day][] = DateFormatter::convertTimeForFront($model->work_start);
            }
            if(isset($model->break_start)){
                $data[$day][] = DateFormatter::convertTimeForFront($model->break_start);
            }
            if(isset($model->break_end)){
                $data[$day][] = DateFormatter::convertTimeForFront($model->break_end);
            }
            if(isset($model->work_end)){
                $data[$day][] = DateFormatter::convertTimeForFront($model->work_end);
            }
        }

        return $data;
    }

    public function getScheduleWhereDayNumber($type)
    {
        $data = [];

        foreach (Schedule::daysForSchedule() as $day){
            $model = $type === Schedule::TYPE_SERVICE
                ? $this->getScheduleServiceDay($day)
                : $this->getScheduleSalonDay($day);
            $data[Schedule::daysForScheduleNumber($day)] = [];
            if(isset($model->work_start)){
                $data[Schedule::daysForScheduleNumber($day)][] = DateFormatter::convertTimeForOclock($model->work_start);
            }
            if(isset($model->break_start)){
                $data[Schedule::daysForScheduleNumber($day)][] = DateFormatter::convertTimeForOclock($model->break_start);
            }
            if(isset($model->break_end)){
                $data[Schedule::daysForScheduleNumber($day)][] = DateFormatter::convertTimeForOclock($model->break_end);
            }
            if(isset($model->work_end)){
                $data[Schedule::daysForScheduleNumber($day)][] = DateFormatter::convertTimeForOclock($model->work_end);
            }
        }

        return $data;
    }

    public function getNameWithBrandAttribute()
    {
        $name = $this->name;

        if($this->brand){
            $name .= ' /' . $this->brand->name;
        }

        return $name;
    }
}



