<?php

namespace App\Models\Forms;

use Database\Factories\Forms\DraftFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $body
 * @property string $path
 * @property int $user_id
 * @method static null|static first()
 * @method static Collection|static[] get()
 * @method static Collection|static[]|LengthAwarePaginator paginate(...$attr)
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static query()
 * @method static Builder|static whereBody($value)
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereId($value)
 * @method static Builder|static where($column, $type = null, $value = null)
 * @method static Builder|static wherePath($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @method static Builder|static whereUserId($value)
 * @method static static create(array $attributes)
 * @mixin Eloquent
 *
 * @method static DraftFactory factory(...$parameters)
 */
class Draft extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'drafts';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'body' => 'array',
    ];

    protected $fillable = [
        'body',
        'path',
    ];
}
