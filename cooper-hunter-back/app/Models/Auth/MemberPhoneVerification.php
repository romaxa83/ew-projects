<?php

namespace App\Models\Auth;

use App\Casts\PhoneCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\ValueObjects\Phone;
use Database\Factories\Auth\MemberPhoneVerificationFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property Phone phone
 * @property string code
 * @property string sms_token
 * @property Carbon sms_token_expires_at
 * @property string|null access_token
 * @property Carbon|null access_token_expires_at
 *
 * @method static MemberPhoneVerificationFactory factory(...$parameters)
 */
class MemberPhoneVerification extends BaseModel
{
    use HasFactory;

    public const TABLE = 'member_phone_verifications';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $casts = [
        'sms_token_expires_at' => 'datetime:Y-m-d H:i:s',
        'access_token_expires_at' => 'datetime:Y-m-d H:i:s',
        'phone' => PhoneCast::class,
    ];
}
