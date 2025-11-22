<?php

namespace App\Models\Menu;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;

/**
 * @property int id
 * @property string title
 * @property int row_id
 * @property string language
 */
class MenuTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'menu_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'row_id',
        'language',
        'title',
    ];
}
