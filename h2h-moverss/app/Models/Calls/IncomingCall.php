<?php

namespace App\Models\Calls;

use App\Enums\ProviderEnum;
use App\Helpers\DbConnections;
use App\Models\Client;
use Database\Factories\Calls\IncomingCallFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

/**
 * App\Models\Calls\IncomingCall
 *
 * @property int $id
 * @property ProviderEnum $provider
 * @property string $call_id
 * @property string $phone
 * @property string|null $client_id
 * @property \Illuminate\Support\Carbon created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @see self::client()
 * @property Client|HasOne client
 *
 * @method static IncomingCallFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class IncomingCall extends Model
{
    use HasFactory;

    protected $connection = DbConnections::DEFAULT;

    public const TABLE = 'incoming_calls';
    protected $table = self::TABLE;

    protected $casts = [
        'provider' => ProviderEnum::class,
    ];

    protected $fillable = [
        'provider',
        'call_id',
        'phone',
        'client_id'
    ];

    protected static function newFactory(): IncomingCallFactory
    {
        return IncomingCallFactory::new();
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
}
