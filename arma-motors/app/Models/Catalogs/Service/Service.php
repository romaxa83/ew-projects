<?php

namespace App\Models\Catalogs\Service;

use App\Models\Dealership\Department;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property null|int $parent_id
 * @property string $alias
 * @property string $icon
 * @property bool $for_guest
 * @property integer $time_step // шаг по времени (в минутах) при подачи заявки
 *
 */
class Service extends BaseModel
{
    use HasFactory;

    const SERVICE_ALIAS = 'service';
    const SERVICE_TO_ALIAS = 'to';
    const SERVICE_DIAGNOSTIC_ALIAS = 'diagnostic';
    const SERVICE_TIRE_ALIAS = 'tire';
    const SERVICE_OTHER_ALIAS = 'other';

    const BODY_ALIAS = 'body';

    const INSURANCE_ALIAS = 'insurance';
    const INSURANCE_CASCO_ALIAS = 'casco';
    const INSURANCE_GO_ALIAS = 'go';
    const INSURANCE_DGO_ALIAS = 'dgo';

    const CREDIT_ALIAS = 'credit';

    const SPARES_ALIAS = 'spares';

    const DEFAULT_TIME_STEP = 30;

    public $timestamps = false;

    public const TABLE = 'services';

    protected $table = self::TABLE;

    protected $fillable = [
        'time_step',
    ];

    protected $casts = [
        'active' => 'bool',
        'for_guest' => 'bool'
    ];

    public static function insuranceCompany(): array
    {
        return [
            'arsenal' => __('translation.service.insurance_company.arsenal'),
            'arks' => __('translation.service.insurance_company.arks')
        ];
    }

    public static function countPayments(): array
    {
        return [
            1 => 1,
            2 => 2,
            3 => 3,
        ];
    }

    // скрвисы у которых есть время записи
    public static function haveRealDate(): array
    {
        return [
            self::BODY_ALIAS,
            self::SERVICE_DIAGNOSTIC_ALIAS,
            self::SERVICE_OTHER_ALIAS,
            self::SERVICE_TIRE_ALIAS,
            self::SERVICE_TO_ALIAS,
        ];
    }

    public function isService(): bool
    {
        return $this->alias === self::SERVICE_ALIAS;
    }

    public function isServiceParent(): bool
    {
        return $this->alias === self::SERVICE_TO_ALIAS
            || $this->alias === self::SERVICE_DIAGNOSTIC_ALIAS
            || $this->alias === self::SERVICE_TIRE_ALIAS
            || $this->alias === self::SERVICE_OTHER_ALIAS;
    }

    public function getOrderDepartment(bool $asType = false): ?string
    {
        if($this->isCasco()
            || $this->isGo()
            || $this->isDgo()
            || $this->isCredit()
            || $this->isInsurance()
        ){
            if($asType){
                return Department::TYPE_CREDIT;
            }
            return Department::DEPARTMENT_CASH;
        }

        if($this->isBody()){
            if($asType){
                return Department::TYPE_BODY;
            }
            return Department::DEPARTMENT_BODY;
        }

        if($this->isDiagnostic()
            || $this->isTire()
            || $this->isTo()
            || $this->isOther()
            || $this->isService()
            || $this->isSpares()
        ){
            if($asType){
                return Department::TYPE_SERVICE;
            }
            return Department::DEPARTMENT_SERVICE;
        }

        return null;
    }

    public function isTo(): bool
    {
        return $this->alias === self::SERVICE_TO_ALIAS;
    }

    public function isDiagnostic(): bool
    {
        return $this->alias === self::SERVICE_DIAGNOSTIC_ALIAS;
    }

    public function isTire(): bool
    {
        return $this->alias === self::SERVICE_TIRE_ALIAS;
    }

    public function isOther(): bool
    {
        return $this->alias === self::SERVICE_OTHER_ALIAS;
    }

    public function isCredit(): bool
    {
        return $this->alias === self::CREDIT_ALIAS;
    }

    public function isBody(): bool
    {
        return $this->alias === self::BODY_ALIAS;
    }

    public function isSpares(): bool
    {
        return $this->alias === self::SPARES_ALIAS;
    }

    public function isInsurance(): bool
    {
        return $this->alias === self::INSURANCE_ALIAS;
    }

    public function isCasco(): bool
    {
        return $this->alias === self::INSURANCE_CASCO_ALIAS;
    }

    public function isGo(): bool
    {
        return $this->alias === self::INSURANCE_GO_ALIAS;
    }

    public function isDgo(): bool
    {
        return $this->alias === self::INSURANCE_DGO_ALIAS;
    }

    // сервисы которые обслуживаются сервисом АА
    public function isRelateToAA(): bool
    {
        return $this->isBody()
            || $this->isSpares()
            || $this->isDiagnostic()
            || $this->isOther()
            || $this->isTire()
            || $this->isTo()
            || $this->isService()
            ;
    }

    // сервисы которые обслуживаются данной системой
    public function isRelateToSystem(): bool
    {
        return $this->isCredit()
            || $this->isInsurance()
            || $this->isCasco()
            || $this->isGo()
            || $this->isDgo()
            ;
    }

    public function isSendToAA(): bool
    {
        return $this->isBody()
            || $this->isSpares()
            || $this->isDiagnostic()
            || $this->isOther()
            || $this->isTire()
            || $this->isTo()
            ;
    }

    // relations

    public function childs(): HasMany
    {
        return $this->hasMany(Service::class, 'parent_id', 'id');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(Service::class, 'id', 'parent_id');
    }

    public function insuranceFranchises(): BelongsToMany
    {
        return $this->belongsToMany(
            InsuranceFranchise::class,
            'service_insurance_franchise_relation',
            'service_id', 'franchise_id'
        );
    }

    public function durations(): BelongsToMany
    {
        return $this->belongsToMany(
            Duration::class,
            'service_duration_service_relation',
            'service_id', 'duration_id'
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class, 'service_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(ServiceTranslation::class,'service_id', 'id')->where('lang', \App::getLocale());
    }

    // scopes

    public function scopeParent(Builder $query)
    {
        return $query->where('parent_id', null);
    }

    public function scopeHaveRealDate(Builder $query, bool $haveRealTime): Builder
    {
        if($haveRealTime){
//            $query->whereIn('alias', self::haveRealDate());
            $query->whereIn('alias', ['diagnostic']);
        }

        return $query;
    }

    // attributes

    public function getInsuranceCompanyAttribute(): array
    {
        if($this->isCasco()){
            return self::insuranceCompany();
        }

        return [];
    }

    public function getCountPaymentsAttribute(): array
    {
        if($this->isCasco()){
            return self::countPayments();
        }

        return [];
    }


    // ACCESSORS

    public function getOrderDepartmentAttribute()
    {
        return $this->getOrderDepartment();
    }
}
