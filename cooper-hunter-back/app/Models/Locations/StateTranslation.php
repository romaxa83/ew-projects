<?php

namespace App\Models\Locations;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Locations\StateTranslationFactory;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property int id
 * @property string name
 * @property int row_id
 * @property string language
 *
 * @method static StateTranslationFactory factory(...$options)
 */
class StateTranslation extends BaseTranslation
{
    use QueryCacheable;
    use HasFactory;

    public const TABLE = 'state_translates';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'language'
    ];

    protected $hidden = [
        'row_id',
    ];
}
