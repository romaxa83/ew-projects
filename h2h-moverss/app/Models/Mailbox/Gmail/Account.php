<?php

namespace App\Models\Mailbox\Gmail;

use App\Helpers\DbConnections;
use App\Models\Division;
use App\User;
use Database\Factories\Gmail\AccountFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany, Relations\HasOne, SoftDeletes};
use Auth, DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Mailbox\Gmail\Account
 *
 * @property int id
 * @property int user_id        //Юзер ID
 * @property int active         //Активность
 * @property int is_archived    //Архивность
 * @property int|null division_id
 * @property mixed|null miscs   //JSON
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mailbox\Gmail\Message[] $messages
 * @property-read int|null messages_count
 * @property-read int|null users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read Division|null $division
 * @property-read User|null $user
 * @method static Builder|Account active()
 * @method static Builder|Account needAuth($user_id)
 * @method static Builder|Account newModelQuery()
 * @method static Builder|Account newQuery()
 * @method static Builder|Account onlyTrashed()
 * @method static Builder|Account query()
 * @method static Builder|Account whereActive($value)
 * @method static Builder|Account whereCreatedAt($value)
 * @method static Builder|Account whereDeletedAt($value)
 * @method static Builder|Account whereId($value)
 * @method static Builder|Account whereMiscs($value)
 * @method static Builder|Account whereUpdatedAt($value)
 * @method static Builder|Account whereUserId($value)
 * @method static Builder|Account withTrashed()
 * @method static Builder|Account withoutTrashed()
 * @method static Builder|Account whereDivisionId($value)
 * @method static Builder|Account accounts($uid, $isAdmin = false)
 * @method static Builder|Account whereIsArchived($value)
 * @method static AccountFactory factory(...$parameters)
 * @property int $id
 * @property int|null $division_id
 * @property int $user_id Юзер ID
 * @property int $active Активность
 * @property int $is_archived
 * @property array|null $miscs JSON
 * @property-read int|null $messages_count
 * @property-read int|null $users_count
 * @mixin \Eloquent
 */
class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = DbConnections::DEFAULT;

    public const TABLE = 'gmail_accounts';
    protected $table = self::TABLE;

    protected $fillable = [
        'miscs',
    ];

    protected $casts = [
        'miscs' => 'json',
    ];

    protected $dates = ['deleted_at'];

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'gmail_accounts_2_users', 'account_id', 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'account_id', 'id');
    }

    public function division(): HasOne
    {
        return $this->hasOne(Division::class, 'id', 'division_id');
    }

    public function scopeActive($q)
    {
        return $q->whereActive(1);
    }

    public function scopeNeedAuth($q, $user_id)
    {
        return $q->active()->whereUserId($user_id)->where('miscs->error_type', 'invalid_grant');
    }

    public function scopeAccounts($q, $uid, $isAdmin = false)
    {
        return $q
            ->with(['users:id', 'division:id,title'])
            ->when(!$isAdmin, function ($q) use ($uid) {
                // if not admin check permissions
                $q->whereUserId($uid)
                    ->orWhere(function ($q) use ($uid) {
                        $q
                            ->active()
                            ->whereHas('users', function ($q) use ($uid) {
                                $q->where('user_id', $uid);
                            });
                    });
            });
    }

    /**
     * Get ids of accounts associated by users.
     * @return array
     * @deprecated
     */
    public function getUserAccountIds(): array
    {
        // Relation does not work correctly in multiple DB
        return DB::connection('mysql')
            ->table('gmail_accounts_2_users')
            ->where('user_id', Auth::id())
            ->get('account_id')
            ->pluck('account_id')
            ->all();
    }
}
