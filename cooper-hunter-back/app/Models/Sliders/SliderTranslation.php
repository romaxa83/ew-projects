<?php

namespace App\Models\Sliders;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Sliders\SliderTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static SliderTranslationFactory factory(...$parameters)
 */
class SliderTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'slider_translations';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'language',
    ];
}
