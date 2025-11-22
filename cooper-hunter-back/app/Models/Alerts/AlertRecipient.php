<?php

namespace App\Models\Alerts;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Alerts\AlertRecipientFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int alert_id
 * @property string recipient_type
 * @property int recipient_id
 * @property bool is_read
 *
 * @method static AlertRecipientFactory factory(...$parameters)
 */
class AlertRecipient extends BaseModel
{
    use HasFactory;

    public const TABLE = 'alert_recipients';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'alert_recipient_key';

    protected $fillable = [
        'alert_id',
        'recipient_id',
        'recipient_type',
        'is_read'
    ];

    protected $casts = [
        'alert_id' => 'int',
        'recipient_id' => 'int',
        'is_read' => 'bool'
    ];

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }
}
