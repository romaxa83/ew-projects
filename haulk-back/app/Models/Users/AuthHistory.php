<?php

namespace App\Models\Users;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $ip
 * @property string|null $exit_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see AuthHistory::user()
 * @property-read User $user
 *
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static query()
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereExitTime($value)
 * @method static Builder|static whereId($value)
 * @method static Builder|static whereIp($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @method static Builder|static whereUserId($value)
 * @mixin Eloquent
 */
class AuthHistory extends Model
{
    public const TABLE = 'auth_history';

    protected $table = self::TABLE;

    protected $fillable = [
        'user_id',
        'ip',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
