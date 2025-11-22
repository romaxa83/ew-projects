<?php

namespace App\Models\Orders\Categories;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;

/**
 * @property int id
 * @property string slug
 * @property string title
 * @property string|null description
 * @property int row_id
 * @property string|null language
 */
class OrderCategoryTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'order_category_translations';
    public $timestamps = false;
    protected $table = self::TABLE;

    protected $fillable = [
        'slug',
        'row_id',
        'language',
        'title',
        'description',
    ];
}
