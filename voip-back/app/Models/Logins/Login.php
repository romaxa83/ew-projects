<?php

namespace App\Models\Logins;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property int id
 * @property string model_type
 * @property int model_id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Login extends BaseModel
{
    protected $table = self::TABLE;
    public const TABLE = 'logins';

    protected $fillable = [];

    protected $casts = [];

    public function model()
    {
        return $this->morphTo();
    }
}

