<?php

namespace App\Models\Catalog\Brands;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Brands\BrandFactory;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property integer id
 * @property string name
 * @property string slug
 *
 * @see Brand::scopeOlmo()
 * @method Builder|static olmo()
 *
 * @see Brand::scopeCooper()
 * @method Builder|static cooper()
 *
 * @method static BrandFactory factory(...$options)
 */
class Brand extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'catalog_brands';
    protected $table = self::TABLE;

    protected $casts = [];

    public function scopeOlmo(Builder|self $builder): void
    {
        $builder->where('slug', ProductOwnerType::OLMO());
    }

    public function scopeCooper(Builder|self $builder): void
    {
        $builder->where('slug', ProductOwnerType::COOPER());
    }
}
