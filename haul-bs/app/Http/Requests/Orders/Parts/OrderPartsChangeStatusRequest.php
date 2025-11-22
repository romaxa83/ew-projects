<?php

namespace App\Http\Requests\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="OrderPartsChangeStatusRequest",
 *     required={"status"},
 *     @OA\Property(property="status", type="string", example="in_process",
 *         enum={"new", "in_process", "sent", "delivered", "canceled", "returned"}
 *     ),
 *     @OA\Property(property="sent_data", description="Sent data, required if status change to sent", type="array",
 *         @OA\Items(ref="#/components/schemas/SentDataRaw")
 *     ),
 * )
 *
 * @OA\Schema(schema="SentDataRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"delivery_method", "delivery_cost", "date_sent"},
 *         @OA\Property(property="delivery_method", type="string", description="Delivery type", example="usps",
 *             enum={"usps", "ups", "fedex", "ltl", "our_delivery"}
 *         ),
 *         @OA\Property(property="delivery_cost", type="number", description="Delivery cost", example="4.3"),
 *         @OA\Property(property="date_sent", type="string", description="Date sent", example="09/12/2023"),
 *         @OA\Property(property="tracking_number", type="string", description="Tracking number", example="09122023"),
 *     )
 * })
 */

class OrderPartsChangeStatusRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', OrderStatus::ruleIn()],
            'sent_data' => ['sometimes','array', 'max:2'],
            'sent_data.*.delivery_method' => ['required_with:sent_data', 'string', DeliveryMethod::ruleIn()],
            'sent_data.*.delivery_cost' => ['required_with:sent_data', 'numeric', 'min:0'],
            'sent_data.*.date_sent' => ['required_with:sent_data', 'date'],
            'sent_data.*.tracking_number' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validationData();
            if($data['status'] == OrderStatus::Sent()){
                if (!array_key_exists('sent_data', $data)) {
                    $validator->errors()->add('sent_data', __('validation.required', ['attribute' => 'sent data']));
                }
            }
        });
    }
}
