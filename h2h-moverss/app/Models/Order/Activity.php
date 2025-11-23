<?php

namespace App\Models\Order;

use App\Enums\Orders\ActivityType;
use App\Models\Order;
use App\User;
use Database\Factories\Orders\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Order\Activity
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property string $type
 * @property mixed|null $miscs
 * @property string|null $ext_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User|null $author
 * @property-read Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereExtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUserId($value)
 * @mixin \Eloquent
 *
 * @method static ActivityFactory factory(...$parameters)
 */
class Activity extends Model
{
    use HasFactory;

    public const MORPH_NAME = 'order-activity';

    public const TABLE = 'orders_activities';
    protected $table = self::TABLE;

    protected $casts = [
        'miscs' => 'json',
    ];

    public function author()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function isStatusType(): bool
    {
        return $this->type === ActivityType::Status->value;
    }

    public function isUserType(): bool
    {
        return $this->type === ActivityType::User->value;
    }

    public function isDivisionType(): bool
    {
        return $this->type === ActivityType::Division->value;
    }

    public function isSourceType(): bool
    {
        return $this->type === ActivityType::Source->value;
    }

    public function isEmailType(): bool
    {
        return $this->type === ActivityType::Email->value;
    }

    public function typeSupportCommunication(): bool
    {
        return in_array($this->type, ActivityType::supportCommunicationPanel());
    }

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
