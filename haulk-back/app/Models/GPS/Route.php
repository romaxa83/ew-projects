<?php

namespace App\Models\GPS;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\GPS\History\RouteFilter;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Filterable;
use Carbon\CarbonImmutable;
use Database\Factories\GPS\RouteFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int|null truck_id
 * @property int|null trailer_id
 * @property int|null device_id
 * @property array|null data
 * @property CarbonImmutable date
 * @property null|string coords_hash
 * @property null|string last_point_id
 *
 * @method static RouteFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Route extends Model
{
    use HasFactory;
    use Filterable;

    public const TABLE_NAME = 'routes';

    protected $connection = DbConnections::GPS;

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'last_point_id'
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'array',
    ];

    public function modelFilter(): string
    {
        return RouteFilter::class;
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Trailer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
