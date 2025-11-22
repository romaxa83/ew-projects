<?php

namespace App\Foundations\Modules\Location\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Location\Factories\StateFactory;
use App\Foundations\Modules\Location\Filters\StateFilter;
use App\Foundations\Traits\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string name
 * @property bool active
 * @property string state_short_name
 * @property string country_code
 * @property string country_name
 * @property int origin_id
 *
 * @method static StateFactory factory(...$parameters)
 */
class State extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'states';
    protected $table = self::TABLE;

    public $timestamps = false;

    public static array $links = [];

    protected $fillable = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'name',
    ];

    public function modelFilter(): string
    {
        return StateFilter::class;
    }

    protected static function newFactory(): StateFactory
    {
        return StateFactory::new();
    }
}
