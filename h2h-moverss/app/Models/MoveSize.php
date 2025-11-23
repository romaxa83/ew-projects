<?php

namespace App\Models;

use Database\Factories\Orders\MoveSizeFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};

/**
 * App\Models\MoveSize
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize newQuery()
 * @method static \Illuminate\Database\Query\Builder|MoveSize onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize query()
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoveSize whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MoveSize withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MoveSize withoutTrashed()
 * @mixin \Eloquent
 * @method static MoveSizeFactory factory(...$parameters)
 */

// todo deprecated
class MoveSize extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'move_sizes';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected static function newFactory(): MoveSizeFactory
    {
        return MoveSizeFactory::new();
    }
}
