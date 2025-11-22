<?php

namespace App\Models\Verify;

use App\Casts\PhoneCast;
use App\Casts\TokenCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsVerify extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'sms_verify';

    protected $table = self::TABLE_NAME;

    protected $dates = [
        'sms_token_expired',
        'action_token_expired'
    ];

    protected $casts = [
        'phone' => PhoneCast::class,
        'sms_token' => TokenCast::class,
        'action_token' => TokenCast::class,
    ];

    public function equalsCode(string $code): bool
    {
        return $this->sms_code === $code;
    }
}

