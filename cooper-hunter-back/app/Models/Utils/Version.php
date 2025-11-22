<?php

namespace App\Models\Utils;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Utils\VersionFactory;

/**
 * @method static VersionFactory factory(...$parameters)
 */
class Version extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'recommended_version',
        'required_version',
    ];
}
