<?php

namespace App\Models\Item;

use Database\Factories\Items\GroupFactory;
use Illuminate\Database\Eloquent\{SoftDeletes, Model, Factories\HasFactory};

/**
 * App\Models\Item\Group
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Query\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Group withoutTrashed()
 * @mixin \Eloquent
 * @method static GroupFactory factory(...$parameters)
 */
class Group extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'items_groups';
    public $timestamps = false;
    protected $fillable = ['title'];
    protected $dates = ['deleted_at'];

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }

    /** test @see \Tests\Unit\Models\Items\Group\AutocompleteTest */
    public function autocomplete($q)
    {
        $qEsc = addslashes($q);
        return $this
            ->where(function ($query) use ($q) {
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
            ->limit(15)
            ->get();
    }

}
