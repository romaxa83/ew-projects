<?php

namespace App\Models\Orders\Deliveries;

use App\Models\BaseTranslation;

/**
 * @property int id
 * @property string slug
 * @property string title
 * @property string|null description
 * @property int row_id
 * @property string|null language
 */
class OrderDeliveryTypeTranslation extends BaseTranslation
{
    public const TABLE = 'order_delivery_type_translations';
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
