<?php

namespace App\Models\Catalogs\Region;

use Illuminate\Database\Eloquent\Model;

class CityTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'city_translations';

    protected $table = self::TABLE;
}
