<?php

namespace App\Models;

use App\Traits\ActiveTrait;
use EloquentFilter\Filterable;

/**
 * @property int $id
 * @property string $name
 * @property bool $active
 */

class Country extends BaseModel
{
    use ActiveTrait;
    use Filterable;

    public $timestamps = false;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
