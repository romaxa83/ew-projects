<?php

namespace App\Models\Order;

use App\Models\Audit;
use App\Models\Client;
use App\Models\Order;
use Database\Factories\Orders\TagFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Exceptions\AuditingException;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\BelongsToMany, SoftDeletes, Model};
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Tag
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Query\Builder|Tag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Tag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tag withoutTrashed()
 * @property string|null $color
 * @property string|null $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|Client[] $clients
 * @property-read int|null $clients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereIcon($value)
 * @property int|null $sort
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereSort($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @mixin \Eloquent
 *
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'orders_tags';
    protected $table = self::TABLE;

    public const BAD_ZIP_ID = 1;
    public const CANT_SERVICE_ID = 8;
    public const NO_ANSWER = 21;

    protected $dates = ['deleted_at'];
    protected $fillable = ['title', 'icon', 'color', 'sort'];

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'orders_2_tags', 'tag_id', 'order_id');
    }

    /**
     * Сохранить теги для заказа.
     * @param  Order  $order
     * @param  array  $tags
     * @return int
     * @throws AuditingException
     */
    public function tagsSaver(Order $order, array $tags): int
    {
        $oldTags = $order->tags->pluck('title')->toArray();

        $ids = [];
        $changed = 0;
        foreach ($tags as $v) {
            $upd = $this->updateOrCreate(
                [
                    'id' => $v['id'] ?? null,
                ],
                [
                    'title' => $v['title']
                ]);
            $ids[] = $upd->id;

            if ($upd->wasChanged() || $upd->wasRecentlyCreated) {
                $changed = 1;
            }
        }

//        $r = $order->tags()->sync($ids);
        $r = $order->auditSync('tags', $ids);

        $orderClone = clone $order;
        $orderClone->refresh();
        $newTags = $orderClone->tags->pluck('title')->toArray();

        $audit = Audit::query()
            ->where('event', Audit::EVENT_SYNC)
            ->where('order_id', $order->id)
            ->latest()
            ->first();

        if($audit){
            $audit->new_values = array_merge($audit->new_values, ['custom_tags' => $newTags]);
            $audit->old_values = array_merge($audit->old_values, ['custom_tags' => $oldTags]);
            $audit->save();
        }

        if ($r['attached'] || $r['detached'] || $r['updated']) {
            $changed = 1;
        }

        return $changed;
    }

}
