<?php

namespace App\Models\Order;

use App\Helpers\DbConnections;
use App\Models\Order;
use Database\Factories\Orders\InventoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Inventory
 *
 * @property int $id
 * @property int $order_id
 * @property int $section_id
 * @property int $is_section
 * @property int|null $item_id
 * @property string|null $title
 * @property string|null $price
 * @property int|null $qty
 * @property string|null $weight
 * @property string|null $volume
 * @property string|null $random_ref
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Inventory[] $children
 * @property-read int|null $children_count
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereIsSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereWeight($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @see self::order()
 * @property Order|BelongsTo order
 * @mixin \Eloquent
 * @method static InventoryFactory factory(...$parameters)
 */
class Inventory extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-inventory';

    public const TABLE = 'orders_inventories';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'order_id',
        'is_section',
        'section_id',
        'item_id',
        'price',
        'qty',
        'weight',
        'volume',
        'title',
        'sort',
        'random_ref'
    ];

    protected $casts = [
        'section_id' => 'integer',
        'is_section' => 'integer',
        'qty' => 'integer',
        'item_id' => 'integer',
        'price' => 'float',
        'weight' => 'float',
        'volume' => 'float',
    ];

    protected static function newFactory(): InventoryFactory
    {
        return InventoryFactory::new();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'section_id', 'id')->orderBy('sort');
    }

    /**
     * Сохранить комнаты и товары.
     * @param  object  $order  Объект заказа
     * @param  array  $records  Данные
     * @return array
     */
    public function saveRecords(object $order, array $records): array
    {
        $ids = [];
        $changed = 0;
        $ref2id = [];
        foreach ($records as $v) {
            $v['section_id'] = 0;

            [$parent, $ids, $changed, $ref2id] = $this->createOrUpdateItem($order, $v, $ids, $changed, $ref2id);

            // Save child
            if (isset($v['children'])) {
                foreach ($v['children'] as $vv) {
                    $vv['is_section'] = 0;
                    $vv['section_id'] = $parent->id;

                    [$nullParent, $ids, $changed, $ref2id] = $this->createOrUpdateItem($order, $vv, $ids, $changed,
                        $ref2id);
                }
            }
        }

        // Удаляем которые не в списке
        $for_delete = $order->inventories()->whereNotIn('id', $ids)->get();
        if ($for_delete && $for_delete->count()) {
            $changed = 1;

            foreach ($for_delete as $v) {
//                    $this->addActivity('client.'.$relation, [
//                        'msg' => $v->value.' was removed',
//                    ]);

                InventoryActivity::saveAsDelete($order, [
                    'id' => $v->id,
                ]);

                $v->delete();
            }
        }

        if (!$changed) {
            return [
                'success' => true,
                'is_changed' => false,
                'msg' => 'Changed nothing',
            ];
        }

        if ($order->sizing_is_auto) {
            $this->recountSizingAuto($order);
        }

        $order = Order::withInventoriesFormat($order->id)->findOrFail($order->id);
        return [
            'success' => true,
            'is_changed' => true,
            'msg' => "Inventory changed ($changed)",
            'record' => $order,
            'ref2id' => $ref2id,
        ];
    }

    /**
     * Сумма позиций для sizing auto.
     * @param $order
     */
    public function recountSizingAuto($order)
    {
        $order->refresh();

        $weight = 0;
        $volume = 0;
        foreach ($order->inventories as $v) {
            if ($v->is_section) {
                continue;
            }

            if ($v->volume) {
                $volume += $v->volume * $v->qty;
            }
            if ($v->weight) {
                $weight += $v->weight * $v->qty;
            }
        }

        $order->sizing_volume = $volume;
        $order->sizing_weight = $weight;
        $order->save();
    }

    /**
     * @param  object  $order
     * @param  mixed  $v
     * @param  array  $ids
     * @param  int  $changed
     * @param  array  $ref2id
     * @return array
     */
    public function createOrUpdateItem(object $order, mixed $v, array $ids, int $changed, array $ref2id): array
    {
        $parent = $order->inventories->where('id', $v['id'])->first();
        if ($parent) {
            $ids[] = $parent->id;

            if ($parent->section_id !== $v['section_id'] ||
                $parent->is_section !== $v['is_section'] ||
                $parent->item_id !== $v['item_id'] ||
                $parent->title !== $v['title'] ||
                $parent->price !== $v['price'] ||
                $parent->qty !== $v['qty'] ||
                $parent->weight !== $v['weight'] ||
                $parent->volume !== $v['volume'] ||
                $parent->sort !== $v['sort']) {

                $parent->fill($v);
                $parent->save();

                InventoryActivity::saveAsUpdate($order, [
                    'id' => $parent->id,
                    'title' => $v['title'],
                    'qty' => $v['qty'],
                ]);

                $changed++;
            }
        } else {
            $v['random_ref'] = $v['randomRef'] ?? null;
            $parent = $order->inventories()
                ->create($v);

            InventoryActivity::saveAsCreate($order, [
                'id' => $parent->id,
                'title' => $v['title'],
                'qty' => $v['qty'],
            ]);

            $changed++;
            $ids[] = $parent->id;
            if (isset($v['randomRef'])) {
                $ref2id[$v['randomRef']] = $parent->id;
            }
        }
        return [$parent, $ids, $changed, $ref2id];
    }

}
