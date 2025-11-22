<?php

namespace App\Http\Resources\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="InvoiceResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="company_name", type="string",),
     *                @OA\Property(property="billing_data", type="object",),
     *                @OA\Property(property="billing_start", type="integer",),
     *                @OA\Property(property="billing_end", type="integer",),
     *                @OA\Property(property="amount", type="float",),
     *                @OA\Property(property="driver_count", type="integer",),
     *                @OA\Property(property="trans_id", type="string",),
     *                @OA\Property(property="pending", type="boolean",),
     *                @OA\Property(property="is_paid", type="boolean",),
     *                @OA\Property(property="paid_at", type="integer",),
     *                @OA\Property(property="public_token", type="string",),
     *                @OA\Property(property="attempt_history", type="object",),
     *                @OA\Property(property="next_attempt_time", type="integer",),
     *                @OA\Property(property="drivers_amount", type="float",),
     *                @OA\Property(property="gps_device_amount", type="float",),
     *                @OA\Property(property="has_gps_subscription", type="boolean",),
     *                @OA\Property(property="gps_device_data", type="array", @OA\Items(ref="#/components/schemas/GpsBillingData")),
     *                @OA\Property(property="gps_device_count", type="integer",),
     *                @OA\Property(property="gps_device_activate_count", type="integer",),
     *                @OA\Property(property="gps_device_deactivate_count", type="integer",),
     *            )
     *        }
     *    ),
     * )
     *
     *  @OA\Schema(
     *      schema="GpsBillingData",
     *      type="object",
     *      allOf={
     *          @OA\Schema(
     *             @OA\Property(property="days", type="integer",),
     *             @OA\Property(property="amount", type="integer",),
     *             @OA\Property(property="activate", type="boolean",),
     *             @OA\Property(property="deactivate", type="boolean",),
     *             @OA\Property(property="active_at", type="integer",),
     *             @OA\Property(property="active_till", type="integer",),
     *             @OA\Property(property="name", type="string",),
     *             @OA\Property(property="imei", type="string",),
     *          )
     *      }
     *  )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'billing_data' => $this->billing_data,
            'billing_start' => strtotime($this->billing_start),
            'billing_end' => strtotime($this->billing_end),
            'amount' => round($this->amount),
            'driver_count' => $this->billing_data
                ? collect($this->billing_data)->last()['driver_count']
                : 0,
            'trans_id' => $this->trans_id,
            'pending' => $this->pending,
            'is_paid' => $this->is_paid,
            'paid_at' => $this->paid_at,
            'public_token' => $this->public_token,
            'attempt_history' => $this->attempt_history,
            'next_attempt_time' => $this->next_attempt_time ? (double) $this->next_attempt_time : null,
            'drivers_amount' => round($this->drivers_amount),
            'gps_device_amount' => round($this->gps_device_amount),
            'has_gps_subscription' => $this->has_gps_subscription,
            'gps_device_data' => $this->gps_device_data,
            'gps_device_count' => collect($this->gps_device_data)->count(),
            'gps_device_activate_count' => collect($this->gps_device_data)->where('activate', true)->count(),
            'gps_device_deactivate_count' => collect($this->gps_device_data)->where('deactivate', true)->count(),
        ];
    }
}
