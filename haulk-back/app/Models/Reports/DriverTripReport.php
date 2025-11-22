<?php

namespace App\Models\Reports;

use App\ModelFilters\Reports\DriverTripReportFilter;
use App\Models\Files\DriverTripReportFile;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Database\Factories\Reports\DriverTripReportFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * App\Models\Users\User
 *
 * @property int $id
 * @property int $driver_id
 * @property Carbon|null $date_from
 * @property Carbon|null $date_to
 * @property Carbon|null $report_date
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read  User|null $driver
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attrs)
 * @method static Builder|static filter($input = [], $filter = null)
 * @method static bool|null forceDelete()
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static onlyTrashed()
 * @method static Builder|static paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static query()
 * @method static bool|null restore()
 * @method static Builder|static simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereDeletedAt($value)
 * @method static Builder|static whereEndsWith($column, $value, $boolean = 'and')
 * @mixin Eloquent
 *
 * @method static DriverTripReportFactory factory(...$parameters)
 */
class DriverTripReport extends Model  implements HasMedia
{
    use Filterable;
    use SetCompanyId;
    use HasMediaTrait;
    use HasFactory;

    public const TABLE_NAME = 'driver_trip_reports';

    public const DRIVER_FILE_FIELD_NAME = 'file';
    public const DRIVER_FILE_COLLECTION_NAME = 'driver_trip_report_files';

    protected $fillable = [
        'driver_id',
        'report_date',
        'date_from',
        'date_to',
    ];

    protected $dates = ['report_date', 'date_from', 'date_to'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function driver(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'driver_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function modelFilter()
    {
        return $this->provideFilter(DriverTripReportFilter::class);
    }

    public function getImageClass(): string
    {
        return DriverTripReportFile::class;

    }
}
