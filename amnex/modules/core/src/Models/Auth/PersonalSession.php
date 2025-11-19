<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Auth;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wezom\Core\Models\Device;

/**
 * \Wezom\Core\Models\Auth\PersonalSession
 *
 * @property int $id
 * @property string $sessionable_type
 * @property int $sessionable_id
 * @property int|null $device_id
 * @property-read Device|null $device
 * @property-read Model|Eloquent $sessionable
 * @property-read Collection<int, PersonalRefreshToken> $refreshTokens
 * @property-read int|null $refresh_tokens_count
 * @method static Builder<static>|PersonalSession newModelQuery()
 * @method static Builder<static>|PersonalSession newQuery()
 * @method static Builder<static>|PersonalSession query()
 * @method static Builder<static>|PersonalSession whereDeviceId($value)
 * @method static Builder<static>|PersonalSession whereId($value)
 * @method static Builder<static>|PersonalSession whereSessionableId($value)
 * @method static Builder<static>|PersonalSession whereSessionableType($value)
 * @mixin Eloquent
 */
class PersonalSession extends Model
{
    public $timestamps = false;

    /**
     * Get the sessionable model that the session belongs to.
     */
    public function sessionable(): MorphTo
    {
        return $this->morphTo('sessionable');
    }

    public function hasDevice(): bool
    {
        return isset($this->device_id);
    }

    public function setDeviceId(?int $device_id): void
    {
        $this->device_id = $device_id;
    }

    /** @return BelongsTo<Device, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function hasIssuedTokens(): bool
    {
        return PersonalRefreshToken::query()->where('session_id', $this->getKey())->exists();
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(PersonalRefreshToken::class, 'session_id');
    }

    public function clearDeviceFcmToken(): void
    {
        if (!$this->hasDevice()) {
            return;
        }

        /** @var Device $device */
        $device = $this->device;

        if ($device->hasFcmToken()) {
            $device->setFcmToken(null);
            $device->save();
        }
    }
}
