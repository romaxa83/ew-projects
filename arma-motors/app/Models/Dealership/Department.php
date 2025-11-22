<?php

namespace App\Models\Dealership;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Models\BaseModel;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property bool $dealership_id
 * @property int $sort
 * @property string $email
 * @property string $phone
 * @property string $telegram
 * @property string $viber
 * @property int $type
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point|null $location
 *
 */
class Department extends BaseModel
{
    use SpatialTrait;

    public const TYPE_SALES   = 1;  // отдел продаж
    public const TYPE_SERVICE = 2;  // сервисный отдел
    public const TYPE_CREDIT  = 3;  // отдел страхования и кредитования
    public const TYPE_BODY    = 4;  // кузовной одел

    public const DEPARTMENT_CASH    = 'departmentCash';  // отдел продаж
    public const DEPARTMENT_BODY    = 'departmentBody';  // сервисный отдел
    public const DEPARTMENT_SALES   = 'departmentSales';  // отдел страхования и кредитования
    public const DEPARTMENT_SERVICE = 'departmentService';  // кузовной одел

    public const TABLE = 'dealership_departments';

    protected $table = self::TABLE;

    protected $spatialFields = [
        'location',
    ];

    protected $casts = [
        'active' => 'bool',
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(DepartmentTranslation::class, 'department_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(DepartmentTranslation::class,'department_id', 'id')->where('lang', \App::getLocale());
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(Schedule::class, 'department_id', 'id');
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}
