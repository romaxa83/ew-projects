<?php

namespace App\Models\Tasks;

use Database\Factories\Tasks\StatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tasks\Status
 *
 * @property int id
 * @property int active        // Активность
 * @property int sort          // Сортировка
 * @property string title      // Название
 * @property string|null class // CSS Класс
 * @property string|null color // Цвет
 * @method static \Illuminate\Database\Eloquent\Builder|Status active()
 * @method static \Illuminate\Database\Eloquent\Builder|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereColor($value)
 * @mixin \Eloquent
 *
 * @method static StatusFactory factory(...$parameters)
 */
class Status extends Model
{
    use HasFactory;

    public const TABLE = 'tasks_statuses';
    protected $table = self::TABLE;

    public const IN_WORK_ID   = 1;
    public const REJECTED_ID  = 2;
    public const COMPLETED_ID = 3;
    public const CANCELED_ID  = 4;

    public $timestamps = false;

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function newFactory(): StatusFactory
    {
        return StatusFactory::new();
    }

    public function scopeActive($q)
    {
        return $q
            ->orderBy('sort')
            ->whereActive(1)
            ->select(['id', 'title', 'class']);
    }
}
