<?php

namespace App\Models\Verify;

use App\Casts\TokenCast;
use App\Exceptions\EmailVerifyException;
use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null entity_type
 * @property int|null entity_id
 * @property string email_token
 * @property Carbon email_token_expires
 * @property boolean verify     // верифицирован по данному токену
 */

class EmailVerify extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE_NAME = 'email_verify';

    protected $table = self::TABLE_NAME;

    protected $dates = [
        'email_token_expired',
    ];

    protected $casts = [
        'email_token' => TokenCast::class,
        'verify' => 'boolean'
    ];

    public function isVerify(): bool
    {
        return $this->verify;
    }

    public function equalsCode(string $code): bool
    {
        return $this->sms_code === $code;
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function getLinkConfirm()
    {
//        return config('app.frontend_url') . "/confirm/email?token=" . $this->email_token->getValue();
        return config('app.frontend_url') . "/verify-email/" . $this->email_token->getValue();
    }

    public static function checkModel(Model $model)
    {
        if($model instanceof User || $model instanceof Admin){
            return true;
        }

        throw new EmailVerifyException(__('error.email_verify.not check model', ['model' => $model::class]));
    }

    public static function getTokenInterval(Model $model)
    {
        if($model instanceof User){
            return config('user.verify_email.email_token_expired');
        }
        if($model instanceof Admin){
            return config('admin.verify_email.email_token_expired');
        }

        throw new EmailVerifyException(__('error.email_verify.not found setting for model', ['model' => $model::class]));
    }

    public static function userEnabled()
    {
        return config('user.verify_email.enabled');
    }

    public static function adminEnabled()
    {
        return config('admin.verify_email.enabled');
    }
}
