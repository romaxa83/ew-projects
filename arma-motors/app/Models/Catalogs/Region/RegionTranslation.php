<?php

namespace App\Models\Catalogs\Region;

use Illuminate\Database\Eloquent\Model;

class RegionTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'region_translations';

    protected $table = self::TABLE;
}
