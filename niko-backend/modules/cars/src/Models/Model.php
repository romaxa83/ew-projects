<?php

namespace WezomCms\Cars\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use WezomCms\Core\Traits\Model\Filterable;
use Yadakhov\InsertOnDuplicateKey;

/**
 *
 * @property int $id
 * @property int $niko_id
 * @property string $name
 * @property int $car_brand_id
 * @property bool $for_trade
 * @mixin \Eloquent
 */
class Model extends EloquentModel
{
    use Filterable;
    use InsertOnDuplicateKey;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'car_models';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'niko_id',
        'car_brand_id',
        'for_trade',
    ];

    protected $casts = [
        'for_trade' => 'bool'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'car_brand_id', 'niko_id');
    }
}
