<?php

namespace App\Http\Requests\Orders\Parts\Ecom;

use App\Dto\Orders\Parts\OrderEcomDto;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Models\Customers\Customer;
use App\Models\Inventories\Inventory;
use App\Rules\Orders\Parts\InventoryQuantity;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderPartsEcomRequest",
 *     required={"sales_manager_id"},
 *     @OA\Property(property="customer_id", type="integer", example="13", description="Customer id"),
 *     @OA\Property(property="client", type="object", ref="#/components/schemas/EcommerceClientRaw"),
 *     @OA\Property(property="items", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderPartsItemsRaw")
 *     ),
 *     @OA\Property(property="delivery_address", type="object", ref="#/components/schemas/AddressRaw"),
 *     @OA\Property(property="billing_address", type="object", ref="#/components/schemas/AddressRaw"),
 *     @OA\Property(property="delivery_type", type="string", example="delivery", description="Delivery type",
 *         enum={"delivery", "pickup"}
 *     ),
 *     @OA\Property(property="payment_method", type="string", example="card", description="Payment method",
 *         enum={"card", "google_pay", "apple_pay", "paypal", "payment_on_pickup"}
 *     ),
 *     @OA\Property(property="with_tax_exemption", type="boolean", example="true"),
 * )
 */
class OrderPartsEcomRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return  [
            'customer_id' => ['nullable', 'int', Rule::exists(Customer::TABLE, 'id')],
            'client' => ['nullable', 'array'],
            'client.first_name' => ['required', 'string'],
            'client.last_name' => ['required', 'string'],
            'client.email' => ['required', 'string', 'email'],
            'items' => ['required', 'array'],
            'items.*.inventory_id' => ['required', 'int', Rule::exists(Inventory::TABLE, 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0', new InventoryQuantity($this)],
            'delivery_type' => ['required', 'string', DeliveryType::ruleIn()],
            'delivery_address' => ['nullable', 'array'],
            'delivery_address.first_name' => ['required_with:delivery_address', 'string', 'alpha', 'max:191'],
            'delivery_address.last_name' => ['required_with:delivery_address', 'string', 'alpha', 'max:191'],
            'delivery_address.address' => ['required_with:delivery_address', 'string', 'max:191'],
            'delivery_address.company' => ['nullable', 'string', 'max:191'],
            'delivery_address.city' => ['required_with:delivery_address', 'string', 'max:191'],
            'delivery_address.state' => ['required_with:delivery_address', 'string', 'max:191'],
            'delivery_address.zip' => ['required_with:delivery_address', 'string', 'max:191'],
            'delivery_address.phone' => ['required_with:delivery_address', new PhoneRule(), 'string', 'max:191'],
            'delivery_address.save' => ['nullable', 'boolean'],
            'billing_address' => ['sometimes', 'array'],
            'billing_address.first_name' => ['required_with:billing_address', 'string', 'alpha', 'max:191'],
            'billing_address.last_name' => ['required_with:billing_address', 'string', 'alpha', 'max:191'],
            'billing_address.address' => ['required_with:billing_address', 'string', 'max:191'],
            'billing_address.company' => ['nullable', 'string', 'max:191'],
            'billing_address.city' => ['required_with:billing_address', 'string', 'max:191'],
            'billing_address.state' => ['required_with:billing_address', 'string', 'max:191'],
            'billing_address.zip' => ['required_with:billing_address', 'string', 'max:191'],
            'billing_address.phone' => ['required_with:billing_address', new PhoneRule(), 'string', 'max:191'],
            'payment_method' => ['required', 'string', PaymentMethod::ruleInForEcom()],
            'with_tax_exemption' => ['required', 'boolean'],
        ];
    }

    public function getDto(): OrderEcomDto
    {
        return OrderEcomDto::byArgs($this->validated());
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validationData();
            if($data['delivery_type'] == DeliveryType::Delivery()){
                if (!array_key_exists('delivery_address', $data) || empty($data['delivery_address'])) {
                    $validator->errors()->add('delivery_address', __('validation.required', ['attribute' => 'Delivery Address']));
                }
            }
            if($data['delivery_type'] == DeliveryType::Pickup()){
                if (!array_key_exists('billing_address', $data) || empty($data['billing_address'])) {
                    $validator->errors()->add('billing_address', __('validation.required', ['attribute' => 'Billing Address']));
                }
            }
        });
    }
}
