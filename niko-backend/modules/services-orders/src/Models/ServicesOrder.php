<?php

namespace WezomCms\ServicesOrders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Regions\Models\City;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Models\ServiceGroup;
use WezomCms\ServicesOrders\Types\OrderStatus;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;

/**
 * \WezomCms\ServicesOrders\Models\ServicesOrder
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $city_id
 * @property int|null $dealership_id
 * @property int|null $car_id
 * @property string|null $is_users_vehicle
 * @property int|null $service_group_id
 * @property int|null $service_id
 * @property \Illuminate\Support\Carbon|null $on_date
 * @property \Illuminate\Support\Carbon|null $final_date
 * @property bool $recall
 * @property bool $read
 * @property string|null $comment
 * @property int|null $rating_services
 * @property int|null $rating_order
 * @property string|null $rating_comment
 * @property \Illuminate\Support\Carbon|null $rate_date
 * @property int $status
 * @property string $mileage
 * @property float|null $final_order_cost // Конечная стоимость, которую заплатил клиент по документу реализации
 * @property float|null $service_discount // Скидка на работы по докуметну реализации
 * @property float|null $spare_parts_discount // Скидка на запчасти по докуметну реализации
 * @property float|null $price_order_cost // Скидка на запчасти по докуметну реализации
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \WezomCms\Services\Models\Service|null $service
 * @method static Builder|ServicesOrder filter($input = array(), $filter = null)
 * @method static Builder|ServicesOrder newModelQuery()
 * @method static Builder|ServicesOrder newQuery()
 * @method static Builder|ServicesOrder paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|ServicesOrder query()
 * @method static Builder|ServicesOrder simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|ServicesOrder whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|ServicesOrder whereCity($value)
 * @method static Builder|ServicesOrder whereCreatedAt($value)
 * @method static Builder|ServicesOrder whereEmail($value)
 * @method static Builder|ServicesOrder whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|ServicesOrder whereId($value)
 * @method static Builder|ServicesOrder whereLike($column, $value, $boolean = 'and')
 * @method static Builder|ServicesOrder whereMessage($value)
 * @method static Builder|ServicesOrder whereName($value)
 * @method static Builder|ServicesOrder wherePhone($value)
 * @method static Builder|ServicesOrder whereServiceId($value)
 * @method static Builder|ServicesOrder whereRead($value)
 * @method static Builder|ServicesOrder whereUpdatedAt($value)
 * @method static Builder|ServicesOrder unread()
 * @method static Builder|ServicesOrder createdStatus()
 * @method static Builder|ServicesOrder notViewed()
 * @method static Builder|ServicesOrder notReject()
 * @mixin \Eloquent
 */
class ServicesOrder extends Model
{
    use Filterable;

//    /**
//     * The attributes that are mass assignable.
//     *
//     * @var array
//     */
//    protected $fillable = ['service_id', 'read', 'name', 'phone', 'email', 'city', 'message'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'recall' => 'bool',
        'read' => 'bool',
        'is_users_vehicle' => 'bool',
    ];

    protected $dates = [
        'on_date',
        'rate_date',
        'final_date'
    ];

    public function isClose()
    {
        return OrderStatus::isDone($this->status);
    }

    public function isSto()
    {
        return $this->group->isSto();
    }

    public function isTestDrive()
    {
        return $this->group->isTestDrive();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function group()
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id', 'id');
    }

    /**
     * @param  Builder  $query
     */
    public function scopeCreatedStatus(Builder $query)
    {
        $query->where('status', OrderStatus::CREATED);
    }

    /**
     * @param  Builder  $query
     */
    public function scopeNotViewed(Builder $query)
    {
        $query->where('read', false);
    }

    /**
     * @param  Builder  $query
     */
    public function scopeNotReject(Builder $query)
    {
        $query->where('status', '!=', OrderStatus::REJECTED);
    }

    public function statusForTable()
    {
        return OrderStatus::renderStatus($this->status);
    }
}
