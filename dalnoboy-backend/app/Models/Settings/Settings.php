<?php

namespace App\Models\Settings;

use App\Models\BaseModel;

class Settings extends BaseModel
{
    public const TABLE = 'settings';

    public $timestamps = false;

    public $fillable = [
        'phone',
        'email',
    ];
}
