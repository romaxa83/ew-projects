<?php

namespace App\Http\Requests\Orders\Parts;

use App\Dto\Orders\Parts\DeliveryDto;
use App\Enums\Orders\Parts\DeliveryMethod;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="OrderPartsDeliveryRequest",
 *     required={"delivery_method", "delivery_cost", "date_sent"},
 *     @OA\Property(property="delivery_method", type="string", description="Delivery method, (can be get here - /api/v1/orders/parts/delivery-methods)", example="1",
 *         enum={"usps", "ups", "fedex", "ltl", "our_delivery"}
 *     ),
 *     @OA\Property(property="delivery_cost", type="number", description="Delivery cost", example="4"),
 *     @OA\Property(property="date_sent", type="string", description="Format Y-m-d", example="2024-04-12"),
 *     @OA\Property(property="tracking_number", type="string", description="Tracking number", example="2024FFR5798"),
 * )
 */

class OrderPartsDeliveryRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'delivery_method' => ['required', 'string', DeliveryMethod::ruleIn()],
            'delivery_cost' => ['required', 'numeric', 'min:0'],
            'date_sent' => ['required', 'date'],
            'tracking_number' => ['nullable', 'string'],
        ];
    }

    public function getDto(): DeliveryDto
    {
        return DeliveryDto::byArgs($this->validated());
    }
}
