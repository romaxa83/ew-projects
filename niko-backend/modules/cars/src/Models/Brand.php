<?php

namespace WezomCms\Cars\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Dealerships\Models\Dealership;
use Yadakhov\InsertOnDuplicateKey;

/**
 *
 * @property int $id
 * @property int $niko_id
 * @property string $name
 * @property int $sort
 * @property bool $published
 * @mixin \Eloquent
 */
class Brand extends Model
{
    use ImageAttachable;
    use PublishedTrait;
    use InsertOnDuplicateKey;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'car_brands';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'niko_id',
        'sort',
        'published',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published' => 'boolean'
    ];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.cars.cars.image_brand'];
    }

    public function getImage()
    {
        return url($this->getImageUrl(null, 'image', null, true));
    }

    public function models()
    {
        return $this->hasMany(\WezomCms\Cars\Models\Model::class, 'car_brand_id', 'niko_id');
    }

    public function modelsForTrade()
    {
        return $this->hasMany(\WezomCms\Cars\Models\Model::class, 'car_brand_id', 'niko_id')
            ->where('for_trade', true);
    }

    public function dealership()
    {
        return $this->hasOne(Dealership::class);
    }
}



