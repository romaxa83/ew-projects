<?php

namespace App\Models;

use Database\Factories\Items\ItemFactory;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\{SoftDeletes, Model, Builder, Collection, Factories\HasFactory};

/**
 * App\Models\Item
 *
 * @property int $id
 * @property array|null $division_ids
 * @property int $group_id
 * @property string $title
 * @property string|null $weight
 * @property string|null $cuft
 * @property float|null $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Item\Group $group
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item selected()
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereCuft($value)
 * @method static Builder|Item whereDeletedAt($value)
 * @method static Builder|Item whereGroupId($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item wherePrice($value)
 * @method static Builder|Item whereTitle($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item whereWeight($value)
 * @method static Builder|Item whereDivisionIds($value)
 * @method static \Illuminate\Database\Query\Builder|Item withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Item withoutTrashed()
 * @property-read Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static ItemFactory factory(...$parameters)
 */
class Item extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'title',
        'group_id',
        'group',
        'weight',
        'cuft',
        'price',
        'division_ids'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'division_ids' => 'json',
    ];

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }

    public function group()
    {
        return $this->hasOne(Item\Group::class, 'id', 'group_id')
            ->withDefault([
                'title' => 'Deleted category',
            ]);
    }

    public function setGroupAttribute($value)
    {
        $this->attributes['group_id'] = $value;
    }

    /**
     * Активные записи.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelected($query)
    {
        return $query->with('group');
    }

    /** test @see \Tests\Unit\Models\Items\AutocompleteTest */
    public function autocomplete($q, int $division_id)
    {
        $qEsc = addslashes($q);
        return $this
            ->with('group')
            ->whereJsonContains('division_ids', $division_id)
            ->where(function (Builder $query) use ($q) {
                if (mb_strlen($q) == 1) {
                    $query->where('title', 'like', "{$q}%");
                } elseif (mb_strlen($q) > 1)
                    $query->where('title', 'like', "%{$q}%");

                $splitted = explode(' ', $q);
                if (count($splitted) > 1) {
                    $query->orWhere(function ($q2) use ($splitted) {
                        foreach ($splitted as $v) {
                            $q2->where('title', 'like', "%{$v}%");
                        }
                    });
                }
            })
            ->orderByRaw("
            CASE
                WHEN `title` LIKE '{$qEsc}%' THEN 1
                WHEN `title` LIKE '%{$qEsc}' THEN 3
                ELSE 2
            END")
            ->orderBy('title')
            ->limit(30)
            ->get();
    }
}
