<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\Location
 *
 * @property int $id
 * @property string $report_id
 * @property string $lat
 * @property string $long
 * @property string $country
 * @property string|null $city
 * @property string $region
 * @property string|null $zipcode
 * @property string|null $street
 * @property string|null $district
 */

class Location extends Model
{
    use HasFactory;

    const TYPE_COUNTRY_FILTER = 'country';
    const TYPE_REGION_FILTER = 'region';
    const TYPE_DISTRICT_FILTER = 'district';

    public $timestamps = false;

    protected $table = 'reports_locations';

    public static function checkTypeForFilter($type)
    {
        return $type == self::TYPE_COUNTRY_FILTER
            || $type == self::TYPE_DISTRICT_FILTER
            || $type == self::TYPE_REGION_FILTER;
    }

    // relation
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
