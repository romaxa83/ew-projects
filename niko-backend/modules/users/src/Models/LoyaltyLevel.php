<?php

namespace WezomCms\Users\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use WezomCms\ServicesOrders\Helpers\Price;

/**
 * @property int $id
 * @property int $segment
 * @property int $level
 * @property int $count_auto
 * @property int $sum_services
 * @property int $discount_sto
 * @property int $discount_spares
 * @method static Builder|Car active()
 */
class LoyaltyLevel extends EloquentModel
{
    public $timestamps = false;

    protected $table = 'loyalties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'segment',
        'level',
        'count_auto',
        'sum_services',
        'discount_sto',
        'discount_spares',
    ];

    public function getSumServices()
    {
        return Price::fromDB($this->sum_services);
    }

    public function getDiscountSto()
    {
        return Price::fromDB($this->discount_sto);
    }

    public function getDiscountSpares()
    {
        return Price::fromDB($this->discount_spares);
    }

    public function setSumServices($sumService)
    {
        $this->sum_services = Price::toDB($sumService);
    }

    public function setDiscountSto($discount)
    {
        $this->discount_sto = Price::toDB($discount);
    }

    public function setDiscountSpares($discount)
    {
        $this->discount_spares = Price::toDB($discount);
    }
}


