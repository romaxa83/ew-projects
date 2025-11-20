<?php

namespace WezomCms\Users\Models;

use WezomCms\ServicesOrders\Helpers\Price;
use WezomCms\Users\Types\LoyaltyType;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property integer $loyalty_type
 * @property integer $loyalty_level
 * @property integer $level_up_amount
 * @property integer $buy_cars
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UserLoyalty extends Model
{
    protected $table = 'user_loyalty';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['loyalty_type', 'loyalty_level', 'level_up_amount', 'buy_cars'];


    public function hasLoyaltyType()
    {
        return LoyaltyType::hasType($this->loyalty_type);
    }

    public function isFamilyType()
    {
        return $this->loyalty_type === LoyaltyType::FAMILY;
    }

    public function getLevelUpAmount()
    {
        return Price::fromDB($this->level_up_amount);
    }

    public function levelInfo()
    {
        return $this->hasOne(LoyaltyLevel::class, 'level', 'loyalty_level');
    }
}
