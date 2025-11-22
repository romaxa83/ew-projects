<?php

namespace App\Models\Commercial\Commissioning;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Commercial\Commissioning\ProtocolTranslationFactory;

/**
 * @property integer id
 * @property string title
 * @property string desc
 * @property int row_id
 * @property string language
 *
 * @method static ProtocolTranslationFactory factory(...$parameters)
 */

class ProtocolTranslation extends BaseTranslation
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'commissioning_protocol_translations';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'desc',
        'row_id',
        'language',
    ];
}
