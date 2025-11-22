<?php

namespace App\Models\Catalog\Products;

use App\Filters\Catalog\ProductKeywordFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Products\ProductKeywordFactory;

/**
 * @property int id
 * @property int product_id
 * @property string keyword
 *
 * @method static ProductKeywordFactory factory()
 */
class ProductKeyword extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'product_keywords';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'keyword'
    ];

    public function modelFilter(): string
    {
        return ProductKeywordFilter::class;
    }
}
