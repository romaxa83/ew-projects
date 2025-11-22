<?php

namespace App\Models\SendPulse;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string token_type
 * @property string access_token
 * @property int expires_in
 *
 * @mixin Eloquent
 */

class AuthToken extends Model
{
    public const TABLE_NAME = 'sendpulse_access_token';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    public $fillable = [
        'token_type',
        'access_token',
        'expires_in',
    ];
}
