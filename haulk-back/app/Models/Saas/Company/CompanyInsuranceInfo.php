<?php

namespace App\Models\Saas\Company;

use App\Models\Files\HasMedia;
use App\Models\Files\SettingImage;
use App\Models\Files\Traits\HasMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int company_id
 */
class CompanyInsuranceInfo extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaTrait;

    public const INSURANCE_FIELD_CARRIER = 'insurance_certificate_image';

    protected $fillable = [
        'insurance_expiration_date',
        'insurance_cargo_limit',
        'insurance_deductible',
        'insurance_agent_name',
        'insurance_agent_phone',
    ];

    protected $casts = [
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getImageClass(): string
    {
        return SettingImage::class;
    }
}
