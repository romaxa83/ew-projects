<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\TireTypeTranslateFactory;

/**
 * @method static TireTypeTranslateFactory factory()
 */
class TireTypeTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'tire_type_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
