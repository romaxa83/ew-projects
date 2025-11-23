<?php

namespace App\Models\Twilio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Twilio\TwilioSmsStatus
 *
 * @property int $id
 * @property string $sid
 * @property string|null $status
 * @property string|null $from
 * @property string|null $to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwilioSmsStatus whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class TwilioSmsStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $table = 'twilio_sms_statuses';
    protected $fillable = [
        'sid', 'status', 'from', 'to'
    ];

}
