<?php

namespace App\Models\Permissions;

use App\Models\BaseTranslation;
use App\Models\Localization\Language;
use App\Traits\HasFactory;
use Database\Factories\Permissions\RoleTranslationFactory;
use Illuminate\Database\Eloquent\Builder;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property int id
 * @property string title
 * @property int row_id
 * @property string language
 * @property-read Language lang
 * @property-read Role row
 *
 * @method static Builder|self query()
 * @method static RoleTranslationFactory factory(...$parameters)
 */
class RoleTranslation extends BaseTranslation
{
    use HasFactory;
    use QueryCacheable;

    public const TABLE = 'roles_translates';

    public $timestamps = false;

    protected $touches = ['row'];

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'language'
    ];

    protected $hidden = [
        'row_id',
    ];

}
