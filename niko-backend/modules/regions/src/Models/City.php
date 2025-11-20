<?php

namespace WezomCms\Regions\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelLocalization;
use Request;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Dealerships\Models\Dealership;

/**
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property string|null $lat
 * @property string|null $lon
 * @property integer $region_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @mixin CityTranslation
 */
class City extends Model
{
    use Translatable;
    use PublishedTrait;
    use Filterable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'lat', 'lon', 'region_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published' => 'bool'
    ];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations', 'region'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function dealership()
    {
        return $this->hasOne(Dealership::class);
    }
}


