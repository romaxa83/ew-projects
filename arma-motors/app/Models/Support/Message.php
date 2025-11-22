<?php

namespace App\Models\Support;

use App\Casts\EmailCast;
use App\Models\BaseModel;
use App\Models\User\User;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $status
 * @property int $category_id
 * @property int|null $user_id
 * @property Email email
 * @property string|null text
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Message extends BaseModel
{
    use HasFactory;

    public const STATUS_DRAFT = 0;
    public const STATUS_READ  = 1;
    public const STATUS_DONE  = 2;

    public const TABLE = 'support_messages';

    protected $table = self::TABLE;

    protected $casts = [
        'email' => EmailCast::class,
    ];

    public static function statusList()
    {
        return [
            self::STATUS_DRAFT => __('translation.support.message.status.draft'),
            self::STATUS_READ => __('translation.support.message.status.read'),
            self::STATUS_DONE => __('translation.support.message.status.done')
        ];
    }

    public static function assertStatus($status): void
    {
        if(!array_key_exists($status, self::statusList())){
            throw new \InvalidArgumentException(__('error.not valid message status', ['status' => $status]));
        }
    }

    public static function checkStatus($status): bool
    {
        return array_key_exists($status, self::statusList());
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class,'category_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }

    // scopes

    public function scopeUserNameSearch(EloquentBuilder $query, string $search)
    {
        return $query->with('user')
            ->whereHas('user', function(EloquentBuilder $q) use ($search) {
                $q->where('name','like', '%' . $search . '%');
            });
    }
}
