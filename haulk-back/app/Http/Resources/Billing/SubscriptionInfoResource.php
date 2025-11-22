<?php

namespace App\Http\Resources\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Company */
class SubscriptionInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="SubscriptionInfoResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="company_name", type="string",),
     *                @OA\Property(property="subscription_active", type="boolean",),
     *                @OA\Property(property="has_payment_method", type="boolean",),
     *                @OA\Property(property="payment_failed", type="boolean",),
     *                @OA\Property(property="attempts_count_exhausted", type="boolean",),
     *                @OA\Property(property="pre_attempts_count_exhausted", type="boolean",description="If there is an invoice with two unpaid attempts"),
     *                @OA\Property(property="has_unpaid_invoices", type="boolean",),
     *                @OA\Property(property="is_trial", type="boolean",),
     *                @OA\Property(property="is_gps_enabled", type="boolean",),
     *            )
     *        }
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'company_name' => $this->getCompanyName(),
            'subscription_active' => $this->isSubscriptionActive(),
            'has_payment_method' => $this->hasPaymentMethod(),
            'payment_failed' => $this->lastPaymentAttemptFailed(),
            'attempts_count_exhausted' => $this->paymentAttemptsCountExhausted(),
            'pre_attempts_count_exhausted' => $this->prePaymentAttemptsCountExhausted(),
            'has_unpaid_invoices' => $this->hasUnpaidInvoices(),
            'is_trial' => $this->isInTrialPeriod(),
            'is_gps_enabled' => $this->isGPSEnabled(),
        ];
    }
}
