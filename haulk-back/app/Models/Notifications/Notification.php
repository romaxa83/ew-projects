<?php

namespace App\Models\Notifications;

use App\Enums\Notifications\NotificationAction;
use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\ModelFilters\Notifications\NotificationFilter;
use App\Services\Notifications\Inner\Patterns\NotificationPatternContract;
use App\Traits\Filterable;
use Carbon\CarbonImmutable;
use Database\Factories\Notifications\NotificationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property NotificationStatus $status
 * @property NotificationType $type
 * @property NotificationPlace $place
 * @property NotificationAction $action
 * @property string $message_key
 * @property array $message_attr
 * @property array $meta
 * @property CarbonImmutable|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static NotificationFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Notification extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'notifications';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'status'
    ];

    protected $casts = [
        'meta' => 'array',
        'status' => NotificationStatus::class,
        'type' => NotificationType::class,
        'place' => NotificationPlace::class,
        'action' => NotificationAction::class,
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'message_attr' => 'array',
    ];

    public function modelFilter(): string
    {
        return NotificationFilter::class;
    }

    public static function create(NotificationPatternContract $pattern)
    {
        $pattern->create();
    }
}
