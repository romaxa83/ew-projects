<?php

namespace App\Models\Fcm;

use App\Models\BaseModel;

class FcmToken extends BaseModel
{
    public $fillable = [
        'member_id',
        'member_type',
        'token',
        'created_at',
        'updated_at',
    ];
}
