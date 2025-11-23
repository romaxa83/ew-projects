<?php

namespace WezomCms\SmsVerify\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Carbon;
use WezomCms\SmsVerify\Casts\TokenCast;
use WezomCms\SmsVerify\ValueObj\Token;

/**
 * @property int $id
 * @property string $phone
 * @property string $code
 * @property Token|null $sms_token
 * @property Carbon|null $sms_token_expires
 * @property Token|null $action_token
 * @property Carbon|null $action_token_expires
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SmsVerify extends EloquentModel
{
    use HasFactory;

    public const TABLE = 'sms_verify';
    protected $table = self::TABLE;

    protected $dates = [
        'sms_token_expired',
        'action_token_expired'
    ];

    protected $casts = [
        'sms_token' => TokenCast::class,
        'action_token' => TokenCast::class,
    ];

    public function equalsCode(string $code): bool
    {
        return $this->code === $code;
    }

}
