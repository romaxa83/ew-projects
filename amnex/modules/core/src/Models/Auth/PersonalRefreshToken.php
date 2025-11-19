<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Auth;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * \Wezom\Core\Models\Auth\PersonalRefreshToken
 *
 * @property int $id
 * @property int $session_id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property int $access_token_id
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PersonalAccessToken $accessToken
 * @property-read PersonalSession $session
 * @property-read Model|Eloquent $tokenable
 * @method static Builder<static>|PersonalRefreshToken newModelQuery()
 * @method static Builder<static>|PersonalRefreshToken newQuery()
 * @method static Builder<static>|PersonalRefreshToken query()
 * @method static Builder<static>|PersonalRefreshToken whereAccessTokenId($value)
 * @method static Builder<static>|PersonalRefreshToken whereCreatedAt($value)
 * @method static Builder<static>|PersonalRefreshToken whereExpiresAt($value)
 * @method static Builder<static>|PersonalRefreshToken whereId($value)
 * @method static Builder<static>|PersonalRefreshToken whereSessionId($value)
 * @method static Builder<static>|PersonalRefreshToken whereToken($value)
 * @method static Builder<static>|PersonalRefreshToken whereTokenableId($value)
 * @method static Builder<static>|PersonalRefreshToken whereTokenableType($value)
 * @method static Builder<static>|PersonalRefreshToken whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PersonalRefreshToken extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    protected $fillable = [
        'token',
        'expires_at',
    ];
    protected $hidden = [
        'token',
    ];

    /**
     * Get the tokenable model that the access token belongs to.
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo('tokenable');
    }

    /** @return BelongsTo<PersonalAccessToken, $this> */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'access_token_id');
    }

    /** @return BelongsTo<PersonalSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PersonalSession::class, 'session_id');
    }

    /**
     * Find the token instance matching the given token.
     */
    public static function findToken(string $token): ?self
    {
        if (!str_contains($token, '|')) {
            return static::query()->where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::query()->find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }

        return null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->lte(now());
    }
}
