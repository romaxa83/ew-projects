<?php

declare(strict_types=1);

namespace Wezom\Core\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * \Wezom\Core\Models\Device
 *
 * @property int $id
 * @property string $name
 * @property string $number
 * @property string|null $fcm_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Device newModelQuery()
 * @method static Builder<static>|Device newQuery()
 * @method static Builder<static>|Device query()
 * @method static Builder<static>|Device whereCreatedAt($value)
 * @method static Builder<static>|Device whereFcmToken($value)
 * @method static Builder<static>|Device whereId($value)
 * @method static Builder<static>|Device whereName($value)
 * @method static Builder<static>|Device whereNumber($value)
 * @method static Builder<static>|Device whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Device extends Model
{
    protected $fillable = ['number', 'name', 'fcm_token'];

    public function getName(): string
    {
        return $this->name;
    }

    public function hasFcmToken(): bool
    {
        return !empty($this->fcm_token);
    }

    public function setFcmToken(?string $fcmToken): void
    {
        $this->fcm_token = $fcmToken;
    }
}
