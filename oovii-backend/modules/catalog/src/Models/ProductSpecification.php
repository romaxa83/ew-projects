<?php

namespace WezomCms\Catalog\Models;

use Cache;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use WezomCms\Catalog\Database\Factories\ProductSpecificationFactory;

/**
 * \WezomCms\Catalog\Models\ProductSpecification
 *
 * @property int $id
 * @property int $product_id
 * @property int $spec_id
 * @property int $spec_value_id
 * @method static ProductSpecificationFactory factory(...$parameters)
 * @method static Builder|ProductSpecification newModelQuery()
 * @method static Builder|ProductSpecification newQuery()
 * @method static Builder|ProductSpecification query()
 * @method static Builder|ProductSpecification whereId($value)
 * @method static Builder|ProductSpecification whereProductId($value)
 * @method static Builder|ProductSpecification whereSpecId($value)
 * @method static Builder|ProductSpecification whereSpecValueId($value)
 * @mixin Eloquent
 */
class ProductSpecification extends EloquentModel
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['spec_id', 'spec_value_id'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(
            function () {
                if (Cache::supportsTags()) {
                    Cache::tags(ProductSpecification::class)->flush();
                }
            }
        );

        static::deleted(
            function () {
                if (Cache::supportsTags()) {
                    Cache::tags(ProductSpecification::class)->flush();
                }
            }
        );
    }
}
