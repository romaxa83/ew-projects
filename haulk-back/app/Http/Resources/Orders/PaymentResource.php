<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Files\FileResource;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="PaymentResourceRaw",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer"),
     *            @OA\Property(property="terms", type="string"),
     *            @OA\Property(property="invoice_id", type="string"),
     *            @OA\Property(property="invoice_notes", type="string"),
     *            @OA\Property(property="invoice_issue_date", type="integer"),
     *            @OA\Property(property="total_carrier_amount", type="number"),
     *            @OA\Property(property="planned_date", type="integer"),
     *            @OA\Property(property="broker_fee_planned_date", type="integer"),
     *            @OA\Property(property="customer_payment_amount", type="number"),
     *            @OA\Property(property="customer_payment_method_id", type="integer"),
     *            @OA\Property(property="customer_payment_method", type="object", allOf={@OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")}),
     *            @OA\Property(property="customer_payment_location", type="string"),
     *            @OA\Property(property="customer_payment_invoice_id", type="string"),
     *            @OA\Property(property="customer_payment_invoice_notes", type="string"),
     *            @OA\Property(property="customer_payment_invoice_issue_date", type="integer"),
     *            @OA\Property(property="broker_payment_amount", type="number"),
     *            @OA\Property(property="broker_payment_method_id", type="integer"),
     *            @OA\Property(property="broker_payment_method", type="object", allOf={@OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")}),
     *            @OA\Property(property="broker_payment_days", type="integer"),
     *            @OA\Property(property="broker_payment_begins", type="string"),
     *            @OA\Property(property="broker_payment_invoice_id", type="string"),
     *            @OA\Property(property="broker_payment_invoice_notes", type="string"),
     *            @OA\Property(property="broker_payment_invoice_issue_date", type="integer"),
     *            @OA\Property(property="broker_fee_amount", type="number"),
     *            @OA\Property(property="broker_fee_method_id", type="integer"),
     *            @OA\Property(property="broker_fee_method", type="object", allOf={@OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")}),
     *            @OA\Property(property="broker_fee_days", type="integer"),
     *            @OA\Property(property="broker_fee_begins", type="string"),
     *            @OA\Property(property="driver_payment_data_sent", type="boolean"),
     *            @OA\Property(property="driver_payment_amount", type="number"),
     *            @OA\Property(property="driver_payment_uship_code", type="string"),
     *            @OA\Property(property="driver_payment_comment", type="string"),
     *            @OA\Property(property="driver_payment_method_id", type="integer"),
     *            @OA\Property(property="driver_payment_method", type="object", allOf={@OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")}),
     *            @OA\Property(property="driver_payment_timestamp", type="integer"),
     *            @OA\Property(property="driver_payment_account_type", type="string"),
     *            @OA\Property(property="driver_payment_check_photo", type="object", allOf={@OA\Schema(ref="#/components/schemas/Image")}),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="PaymentResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        description="Order payment data",
     *        allOf={
     *            @OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")
     *        }
     *    )
     * )
     *
     */
    public function toArray($request)
    {
        /** @var Payment $payment */
        $payment = $this;
        $invoice = implode(
            ',',
            array_filter(
                [
                    $payment->invoice_id,
                    $payment->customer_payment_invoice_id,
                    $payment->broker_payment_invoice_id
                ],
                static fn($item): bool => !empty($item)
            )
        );
        return [
            'id' => $payment->id,
            'terms' => $payment->terms,

            'invoice_id' => !empty($invoice) ? $invoice : null,
            'invoice_notes' => $payment->invoice_notes,
            'invoice_issue_date' => $payment->invoice_issue_date,

            'total_carrier_amount' => $payment->total_carrier_amount ? (double)$payment->total_carrier_amount : null,

            'broker_fee_planned_date' => $payment->broker_fee_planned_date,

            'customer_payment_amount' => $payment->customer_payment_amount ? (double)$payment->customer_payment_amount : null,
            'customer_payment_method_id' => $payment->customer_payment_method_id ? (int)$payment->customer_payment_method_id : null,
            'customer_payment_method' => $payment->customer_payment_method_id ? [
                'id' => (int)$payment->customer_payment_method_id,
                'title' => Payment::CUSTOMER_METHODS[$payment->customer_payment_method_id] ?? '',
            ] : null,
            'customer_payment_location' => $payment->customer_payment_location,
            'customer_payment_invoice_id' => $payment->customer_payment_invoice_id,
            'customer_payment_invoice_issue_date' => $payment->customer_payment_invoice_issue_date ? (int)$payment->customer_payment_invoice_issue_date : null,
            'customer_payment_invoice_notes' => $payment->customer_payment_invoice_notes,

            'broker_payment_amount' => $payment->broker_payment_amount ? (double)$payment->broker_payment_amount : null,
            'broker_payment_method_id' => $payment->broker_payment_method_id ? (int)$payment->broker_payment_method_id : null,
            'broker_payment_method' => $payment->broker_payment_method_id ? [
                'id' => (int)$payment->broker_payment_method_id,
                'title' => Payment::BROKER_METHODS[$payment->broker_payment_method_id] ?? '',
            ] : null,
            'broker_payment_days' => $payment->broker_payment_days,
            'broker_payment_begins' => $payment->broker_payment_begins,
            'broker_payment_invoice_id' => $payment->broker_payment_invoice_id,
            'broker_payment_invoice_issue_date' => $payment->broker_payment_invoice_issue_date ? (int)$payment->broker_payment_invoice_issue_date : null,
            'broker_payment_invoice_notes' => $payment->broker_payment_invoice_notes,

            'broker_fee_amount' => $payment->broker_fee_amount ? (double)$payment->broker_fee_amount : null,
            'broker_fee_method_id' => $payment->broker_fee_method_id ? (int)$payment->broker_fee_method_id : null,
            'broker_fee_method' => $payment->broker_fee_method_id ? [
                'id' => (int)$payment->broker_fee_method_id,
                'title' => Payment::BROKER_METHODS[$payment->broker_fee_method_id] ?? '',
            ] : null,
            'broker_fee_days' => $payment->broker_fee_days,
            'broker_fee_begins' => $payment->broker_fee_begins,

            'driver_payment_data_sent' => (bool)$payment->driver_payment_data_sent,
            'driver_payment_amount' => $payment->driver_payment_amount ? (double)$payment->driver_payment_amount : null,
            'driver_payment_uship_code' => $payment->driver_payment_uship_code,
            'driver_payment_comment' => $payment->driver_payment_comment,
            'driver_payment_method_id' => $payment->driver_payment_method_id ? (int)$payment->driver_payment_method_id : null,
            'driver_payment_method' => $payment->driver_payment_method_id ? [
                'id' => (int)$payment->driver_payment_method_id,
                'title' => Payment::CUSTOMER_METHODS[$payment->driver_payment_method_id] ?? '',
            ] : null,
            'driver_payment_timestamp' => $payment->driver_payment_timestamp,
            'driver_payment_account_type' => $payment->driver_payment_account_type,
            Order::DRIVER_PAYMENT_FIELD_NAME => $payment->relationLoaded('media') ? FileResource::make(
                $payment->getFirstMedia(Order::DRIVER_PAYMENT_COLLECTION_NAME)
            ) : null
        ];
    }
}
