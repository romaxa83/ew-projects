<?php

namespace App\Models\Tasks;

use Database\Factories\Tasks\TypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tasks\Type
 *
 * @property int $id
 * @property int $active
 * @property int $sort
 * @property string $title
 * @property string|null $icon
 * @property string|null $color
 * @method static \Illuminate\Database\Eloquent\Builder|Type active()
 * @method static \Illuminate\Database\Eloquent\Builder|Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type query()
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereTitle($value)
 * @mixin \Eloquent
 *
 * @method static TypeFactory factory(...$parameters)
 */
class Type extends Model
{
    use HasFactory;

    public const TABLE = 'tasks_types';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'icon',
        'color',
        'sort',
        'active'
    ];

    public function scopeActive($q)
    {
        return $q
            ->orderBy('sort')
            ->whereActive(1)
            ->select(['id', 'title', 'icon', 'color']);
    }

    protected static function newFactory(): TypeFactory
    {
        return TypeFactory::new();
    }
}
