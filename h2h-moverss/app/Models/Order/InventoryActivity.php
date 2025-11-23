<?php

namespace App\Models\Order;

use App\Enums\ActionEnum;
use App\Helpers\DbConnections;
use App\Models\Audit;
use App\Models\Client;
use App\Models\Order;
use App\Services\Communications\RecordCreateService;
use App\User;
use Database\Factories\Orders\InventoryActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 * @property int id
 * @property int order_id
 * @property int|null client_id
 * @property int|null user_id
 * @property int|null inventory_id
 * @property ActionEnum action
 * @property bool is_client_action // совершил ли клиент данные действия
 * @property array miscs
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::order()
 * @property Order|BelongsTo order
 *
 * @see self::client()
 * @property Client|BelongsTo client
 *
 * @see self::user()
 * @property User|BelongsTo user
 *
 * @mixin \Eloquent
 * @method static InventoryActivityFactory factory(...$parameters)
 */
class InventoryActivity extends Model
{
    use HasFactory;

    public const MORPH_NAME = 'order-inventory-activity';

    public const TABLE = 'orders_inventory_activities';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'order_id',
        'client_id',
        'user_id',
        'inventory_id',
        'is_client_action',
        'action',
        'miscs',
    ];

    protected $casts = [
        'miscs' => 'array',
        'is_client_action' => 'boolean',
        'action' => ActionEnum::class,
    ];

    protected static function newFactory(): InventoryActivityFactory
    {
        return InventoryActivityFactory::new();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createRecords(Order $order, array $data)
    {
        $tmpIds = [];
        $orderInventoryIds = $order->inventories->pluck('id')->toArray();
        foreach ($data as $item) {
            if(is_null($item['id'])){
                $this->saveAsCreate($order, $item);
            } else {
                $tmpIds[] = $item['id'];
                $this->saveAsUpdate($order, $item);
            }
        }

        foreach (array_diff($orderInventoryIds, $tmpIds) as $id) {
            $this->saveAsDelete($order, ['id' => $id]);
        }

    }

    public static function saveAsCreate(Order $order, array $data)
    {
        $model = new self();
        $model->order_id = $order->id;
        $model->client_id = $order->client_id;
        $model->action = ActionEnum::Create();
        $model->user_id = auth_user()?->id;
        $model->is_client_action = $model->user_id
            ? false
            : true;
        $model->miscs = [
            'inventory_id' => $data['id'],
            'title' => $data['title'],
            'qty' => $data['qty'],
        ];
        $model->save();

        RecordCreateService::handler($model);
    }

    public static function saveAsUpdate(Order $order, array $data)
    {
        $activity = InventoryActivity::query()
            ->where('order_id', $order->id)
            ->whereJsonContains('miscs', ['inventory_id' => $data['id']])
            ->latest()
            ->first();

        if($activity
            && $activity->miscs['qty'] == $data['qty']
            && $activity->miscs['title'] == $data['title']
        ) {
            return;
        }

        $model = new self();
        $model->order_id = $order->id;
        $model->client_id = $order->client_id;
        $model->action = ActionEnum::Update();
        $model->user_id = auth_user()?->id;
        $model->is_client_action = $model->user_id
            ? false
            : true;
        $model->miscs = [
            'inventory_id' => $data['id'],
            'title' => $data['title'],
            'qty' => $data['qty'],
        ];

        $model->save();

        RecordCreateService::handler($model);
    }

    public static function saveAsDelete(Order $order, array $data)
    {
        $activity = InventoryActivity::query()
            ->where('order_id', $order->id)
            ->whereJsonContains('miscs', ['inventory_id' => $data['id']])
            ->latest()
            ->first();

        $model = new self();
        $model->order_id = $order->id;
        $model->client_id = $order->client_id;
        $model->action = ActionEnum::Delete();
        $model->user_id = auth_user()?->id;
        $model->is_client_action = $model->user_id
            ? false
            : true;

        if($activity){
            $model->miscs = $activity->miscs;
        } else {
            $model->miscs = [
                'inventory_id' => $data['id'],
                'title' => $data['title'] ?? null,
                'qty' => $data['qty'] ?? null,
            ];
        }

        $model->save();

        RecordCreateService::handler($model);

        $audit = Audit::query()
            ->where('auditable_type', Inventory::MORPH_NAME)
            ->where('auditable_id', $data['id'])
            ->where('event', Audit::EVENT_DELETED)
            ->first();

        if($audit){
            $audit->order_id = $order->id;
            $audit->client_id = $order->client_id;
            $audit->save();
        }
    }
}
