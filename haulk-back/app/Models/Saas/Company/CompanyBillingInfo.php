<?php

namespace App\Models\Saas\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int oid
 * @property int company_id
 */
class CompanyBillingInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_phone',
        'billing_phone_name',
        'billing_phone_extension',
        'billing_phones',
        'billing_email',
        'billing_payment_details',
        'billing_terms',
    ];

    protected $casts = [
        'billing_phones' => 'array',
    ];

    public $timestamps = false;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
