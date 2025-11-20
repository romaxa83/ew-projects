<?php

namespace WezomCms\Dealerships\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property int $city_id
 * @property string $locale
 * @property string|null $name
 * @property string|null $text
 * @property string|null $address
 * @property string|null $services
 * @mixin \Eloquent
 */
class DealershipTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'dealership_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'text', 'address', 'services'];
}
