<?php
namespace App\Models;

use Database\Factories\Orders\BuildingTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BuildingType
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType newQuery()
 * @method static \Illuminate\Database\Query\Builder|BuildingType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType query()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildingType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|BuildingType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BuildingType withoutTrashed()
 * @mixin \Eloquent
 * @method static BuildingTypeFactory factory(...$parameters)
 */

// todo удалить после перехода на enum
class BuildingType extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'building_types';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected static function newFactory(): BuildingTypeFactory
    {
        return BuildingTypeFactory::new();
    }
}
