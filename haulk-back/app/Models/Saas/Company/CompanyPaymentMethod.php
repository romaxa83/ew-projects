<?php

namespace App\Models\Saas\Company;

use App\Dto\Payments\AuthorizeNet\AuthorizeNetMemberProfileDto;
use App\Dto\Payments\PaymentDataAbstract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPaymentMethod extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'payment_data' => 'json',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function hasPaymentData(): bool
    {
        return !empty($this->payment_data) && !is_null($this->payment_data);
    }

    public function getPaymentData(): PaymentDataAbstract
    {
        return new AuthorizeNetMemberProfileDto(
            $this->payment_data
        );
    }
}
