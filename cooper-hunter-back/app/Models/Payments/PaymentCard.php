<?php

namespace App\Models\Payments;

use App\Contracts\Utilities\Dispatchable;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Payments\PaymentCardFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property integer id
 * @property string|null guid
 * @property string member_type
 * @property int member_id
 * @property string code
 * @property string type
 * @property string expiration_date
 * @property boolean default
 * @property string hash
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static PaymentCardFactory factory(...$options)
 */
class PaymentCard extends BaseModel implements Dispatchable
{
    use HasFactory;

    public const TABLE = 'payment_cards';
    protected $table = self::TABLE;
    public const MORPH_NAME = 'payment_card';

    protected $fillable = [
        'guid',
        'member_id',
        'member_type',
        'default'
    ];

    protected $casts = [
        'default' => 'boolean',
    ];

    public function member(): MorphTo
    {
        return $this->morphTo();
    }
}
