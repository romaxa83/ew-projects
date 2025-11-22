<?php

namespace App\Models\Order;

use App\Casts\MoneyCast;
use App\Models\AA\AAPost;
use App\Models\Agreement\Agreement;
use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Catalogs\Car\TransportType;
use App\Models\Catalogs\Region\City;
use App\Models\Catalogs\Region\Region;
use App\Models\Catalogs\Service\DriverAge;
use App\Models\Catalogs\Service\Duration;
use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Models\Catalogs\Service\Privileges;
use App\Models\Dealership\Dealership;
use App\Models\Recommendation\Recommendation;
use App\Models\User\Car;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $car_id
 * @property int|null $dealership_id
 * @property int|null $franchise_id
 * @property int|null $brand_id
 * @property int|null $model_id
 * @property int|null $region_id
 * @property int|null $city_id
 * @property int|null $transport_type_id
 * @property int|null $privileges_id
 * @property int|null $driver_age_id
 * @property int|null $duration_id
 * @property int|null $agreement_id                 // доп. соглашение на основе которой создана заявка
 * @property int|null $recommendation_id            // рекомендация на основе которой создана заявка
 * @property string|null $responsible               // ответственное лицо
 * @property string|null $insurance_company         // страховая компания
 * @property int|null $car_cost                     // стоимость авто
 * @property int|null $count_pay                    // кол-во платежей
 * @property bool $use_as_taxi                      // использовалось в такси
 * @property int|null $type_user                    // тип пользователя (юр/физ лицо)
 * @property int|null $first_installment_percent    // первый платеж в процентах
 * @property int|null $rate                         // оценка
 * @property string|null $rate_comment              // комментарий к оценки
 * @property string|null $comment                   // комментарий к заявки
 * @property int|null $mileage                      // пробег авто (актуально для сервисов)
 * @property Carbon|null $on_date                   // на какую дату заявка, желаемая дата (актуально для сервисов)
 * @property Carbon|null $real_date                 // на какую дату заявка, фактическое время (актуально для сервисов)
 * @property Carbon|null $for_current_filter_date   // дата по которой фильтруются заявки для пользователя, сначала записывается onDate когда появляется realDate, перезаписывается на нее
 * @property Carbon|null $is_send_remind            // было ли отправленно уведомление
 * @property string|null $post_uuid                 // uuid поста на который была записана данная звявка
 *
 * @property-read Order|null order
 * @property-read InsuranceFranchise|null franchise
 * @property-read Brand|null brand
 * @property-read Car|null car
 * @property-read Dealership|null dealership
 * @property-read Model|null model
 * @property-read DriverAge|null driverAge
 * @property-read Region|null region
 * @property-read City|null city
 * @property-read TransportType|null transportType
 * @property-read Privileges|null privileges
 * @property-read Duration|null duration
 * @property-read Recommendation|null recommendation
 * @property-read AAPost|null post
 */
class Additions extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE_NAME = 'order_additions';
    protected $table = self::TABLE_NAME;

    protected $casts = [
        'use_as_taxi' => 'boolean',
        'is_send_remind' => 'boolean',
        'car_cost' => MoneyCast::class,
    ];

    protected $fillable = [
        'is_send_remind'
    ];

    protected $dates = [
        'on_date',
        'real_date',
        'for_current_filter_date',
    ];

    // relation

    public function order(): BelongsTo|Order
    {
        return $this->belongsTo(Order::class);
    }

    public function franchise(): BelongsTo|InsuranceFranchise
    {
        return $this->belongsTo(InsuranceFranchise::class);
    }

    public function brand(): BelongsTo|Brand
    {
        return $this->belongsTo(Brand::class);
    }

    public function car(): BelongsTo|Car
    {
        return $this->belongsTo(Car::class);
    }

    public function dealership(): BelongsTo|Dealership
    {
        return $this->belongsTo(Dealership::class);
    }

    public function model(): BelongsTo|Model
    {
        return $this->belongsTo(Model::class);
    }

    public function driverAge(): BelongsTo|DriverAge
    {
        return $this->belongsTo(DriverAge::class);
    }

    public function region(): BelongsTo|Region
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo|City
    {
        return $this->belongsTo(City::class);
    }

    public function transportType(): BelongsTo|TransportType
    {
        return $this->belongsTo(TransportType::class);
    }

    public function privileges(): BelongsTo|Privileges
    {
        return $this->belongsTo(Privileges::class);
    }

    public function duration(): BelongsTo|Duration
    {
        return $this->belongsTo(Duration::class);
    }

    public function recommendation(): BelongsTo|Recommendation
    {
        return $this->belongsTo(Recommendation::class);
    }

//    public function agreement(): BelongsTo|Agreement
//    {
//        return $this->belongsTo(Agreement::class);
//    }

    public function post(): BelongsTo|AAPost
    {
        return $this->belongsTo(AAPost::class, 'post_uuid', 'uuid');
    }
}



