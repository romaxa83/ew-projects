<?php

namespace App\Models\Communications;

use App\Models\Client;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string channel_contact
 * @property int|null client_id
 * @property float score
 * @property string details
 * @property string call_id
 * @property CarbonImmutable created_at
 * @property CarbonImmutable updated_at
 * @mixin \Eloquent
 */
class CallInfo extends Model
{
    public const MORPH_NAME = 'communication-call-info';

    public const TABLE = 'communication_call_info';
    protected $table = self::TABLE;

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}