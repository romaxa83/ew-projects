<?php

namespace Wezom\Core\Models\Auth;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Wezom\Core\Database\Factories\Auth\GuestSessionFactory;
use Wezom\Core\Exceptions\TranslatedException;

/**
 * \Wezom\Core\Models\Auth\GuestSession
 *
 * @property int $id
 * @property string $session
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static GuestSessionFactory factory($count = null, $state = [])
 * @method static Builder<static>|GuestSession newModelQuery()
 * @method static Builder<static>|GuestSession newQuery()
 * @method static Builder<static>|GuestSession query()
 * @method static Builder<static>|GuestSession whereCreatedAt($value)
 * @method static Builder<static>|GuestSession whereExpiresAt($value)
 * @method static Builder<static>|GuestSession whereId($value)
 * @method static Builder<static>|GuestSession whereSession($value)
 * @method static Builder<static>|GuestSession whereUpdatedAt($value)
 * @mixin Eloquent
 */
class GuestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session',
        'expires_at',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getSession(): string
    {
        return $this->session;
    }

    public static function findBySession(string $session): ?self
    {
        return static::where('session', $session)->first();
    }

    public static function findBySessionOrFail(string $session): self
    {
        $session = static::findBySession($session);

        if (!$session) {
            throw new TranslatedException(__('core::exceptions.session_not_found'));
        }

        return $session;
    }
}
