<?php

namespace App\Http\Resources\Reports;

use App\Documents\CompanyDocument;
use App\Http\Resources\Orders\PaymentStageResource;
use App\Models\Orders\PaymentStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin CompanyDocument
 */
class CompanyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="CompanyReportResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Report data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"full_name"},
     *                        @OA\Property(property="company_name", type="string", description=""),
     *                        @OA\Property(property="total_count", type="integer", description=""),
     *                        @OA\Property(property="total_due_count", type="number", description=""),
     *                        @OA\Property(property="past_due_count", type="integer", description=""),
     *                        @OA\Property(property="total_due", type="number", description=""),
     *                        @OA\Property(property="past_due", type="number", description=""),
     *                        @OA\Property(property="current_due", type="number", description=""),
     *                        @OA\Property(property="latest_payment_date", type="number", description=""),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        return [
            'company_name' => Str::upper($this->companyName),
            'total_count' => $this->orderCount,
            'total_due_count' => $this->totalDueCount,
            'past_due_count' => $this->past_due_count,
            'total_due' => $this->totalDue,
            'past_due' => $this->past_due,
            'current_due' => $this->current_due,
            'last_payment_stage' => $this->lastPaymentStageId ? PaymentStageResource::make(
                PaymentStage::find($this->lastPaymentStageId)
            ) : null,
        ];
    }
}
