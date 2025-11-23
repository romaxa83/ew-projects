<?php

namespace App\Models\Vapi;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $call_rec_id
 * @property int|null $client_id
 * @property string $caller_number
 * @property string|null $client_name
 * @property string|null $client_number
 * @property string $department_type
 * @property array|null $misc
 * @property string $call_back_at
 * @property string|null $pickup_location
 * @property string|null $pickup_stairs
 * @property string|null $delivery_location
 * @property string|null $delivery_stairs
 * @property string|null $additional
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @mixin \Eloquent
 *
 * @see self::client()
 * @property-read Client|null $client
 */
class ClientRequest extends Model
{
    public const TABLE = 'vapi_client_requests';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'vapi-client-request';

    protected $dates = [];

    protected $fillable = [
        'call_rec_id'
    ];

    protected $casts = [
        'misc' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
