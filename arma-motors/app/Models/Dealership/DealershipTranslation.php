<?php

namespace App\Models\Dealership;

use Illuminate\Database\Eloquent\Model;

class DealershipTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'dealership_translations';

    protected $table = self::TABLE;
}

