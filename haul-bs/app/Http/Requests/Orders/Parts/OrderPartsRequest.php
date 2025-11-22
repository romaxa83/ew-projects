<?php

namespace App\Http\Requests\Orders\Parts;

use App\Dto\Orders\Parts\OrderDto;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Rules\Orders\Parts\HasOverloadRule;
use App\Rules\Orders\Parts\RequiredDeliveryAddress;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * @OA\Schema(type="object", title="OrderPartsRequest",
 *     required={"customer_id", "delivery_address", "items"},
 *     @OA\Property(property="customer_id", type="integer", example="13", description="Customer id"),
 *     @OA\Property(property="delivery_cost", type="number", example="13", description="Custom delivery cost by order"),
 *     @OA\Property(property="delivery_address", type="object", ref="#/components/schemas/AddressRawWithSave"),
 *     @OA\Property(property="billing_address", type="object", ref="#/components/schemas/AddressRaw"),
 *     @OA\Property(property="payment", type="object", ref="#/components/schemas/OrderPartsPaymentRaw"),
 *     @OA\Property(property="source", type="string", example="bs", enum={"bs", "amazon", "haulk_depot"},
 *         description="Order source, (can be get here - /api/v1/orders/parts/catalog/sources)"
 *     ),
 *     @OA\Property(property="delivery_type", type="string", example="pickup", enum={"delivery", "pickup"},
 *          description="Order delivery type, (can be get here - /api/v1/orders/parts/catalog/delivery-types)"
 *     ),
 * )
 *
 * @OA\Schema(schema="OrderPartsItemsRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"inventory_id", "quantity"},
 *         @OA\Property(property="inventory_id", type="integer", description="Inventory id", example="1"),
 *         @OA\Property(property="quantity", type="number", description="quantity", example="4"),
 *     )
 * })
 *
 * @OA\Schema(schema="OrderPartsPaymentRaw", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="terms", type="string", example="immediately", enum={"immediately", "day_15", "day_30"},
 *             description="Payment terms, (can be get here - /api/v1/orders/parts/catalog/payment-terms)"
 *         ),
 *         @OA\Property(property="method", type="string", example="cash",
 *             description="Payment terms, (can be get here - /api/v1/orders/parts/catalog/payment-methods)"
 *         ),
 *         @OA\Property(property="with_tax_exemption", type="boolean", example="true"),
 *     )
 * })
 *
 * @OA\Schema(schema="OrderPartsShippingMethodRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"name", "cost"},
 *         @OA\Property(property="name", type="string", example="UPS Standard",
 *             description="Shipping Method, (can be get here - /api/v1/orders/parts/shipping-methods)"
 *         ),
 *         @OA\Property(property="cost", type="string", example="27.05"),
 *         @OA\Property(property="terms", type="string", example="2 business day"),
 *         @OA\Property(property="items_ids", type="array", @OA\Items(type="integer")),
 *     ),

 * })
 *
 * @OA\Schema(schema="AddressRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"first_name", "last_name", "address", "city", "state", "zip", "phone"},
 *         @OA\Property(property="customer_address_id", type="integer", example="2"),
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="company", type="string", example="Sony Inc."),
 *         @OA\Property(property="address", type="string", example="801 West Dundee Road"),
 *         @OA\Property(property="city", type="string", example="Arlington Heights"),
 *         @OA\Property(property="state", type="string", example="CA"),
 *         @OA\Property(property="zip", type="string", example="60004"),
 *         @OA\Property(property="phone", type="string", example="1555555555"),
 *     )
 * })
 * @OA\Schema(schema="AddressRawWithSave", type="object", allOf={
 *      @OA\Schema(
 *          required={"first_name", "last_name", "address", "city", "state", "zip", "phone"},
 *          @OA\Property(property="customer_address_id", type="integer", example="2"),
 *          @OA\Property(property="first_name", type="string", example="John"),
 *          @OA\Property(property="last_name", type="string", example="Doe"),
 *          @OA\Property(property="company", type="string", example="Sony Inc."),
 *          @OA\Property(property="address", type="string", example="801 West Dundee Road"),
 *          @OA\Property(property="city", type="string", example="Arlington Heights"),
 *          @OA\Property(property="state", type="string", example="CA"),
 *          @OA\Property(property="zip", type="string", example="60004"),
 *          @OA\Property(property="phone", type="string", example="1555555555"),
 *          @OA\Property(property="save", type="boolean", description="Save address to customer"),
 *      )
 *  })
 */

class OrderPartsRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    protected Order|null $order = null;

    public function rules(): array
    {
        return  [
            'customer_id' => [
                Rule::requiredIf(function () {
                    return !$this->order->source->isHaulkDepot();
                }),
                'int', Rule::exists(Customer::TABLE, 'id')],
            'source' => ['required', 'string', OrderSource::ruleIn()],
            'delivery_type' => ['nullable', 'string', DeliveryType::ruleIn(), new HasOverloadRule($this->getOrder())],
            'delivery_address' => ['sometimes', 'array'],
            'delivery_address.customer_address_id' => ['nullable', 'int', Rule::exists(Address::TABLE, 'id')],
            'delivery_address.first_name' => ['required_if:delivery_address.customer_address_id,null', 'string', 'alpha', 'max:191'],
            'delivery_address.last_name' => ['required_if:delivery_address.customer_address_id,null', 'string', 'alpha', 'max:191'],
            'delivery_address.address' => ['required_if:delivery_address.customer_address_id,null', 'string', 'max:191'],
            'delivery_address.company' => ['nullable', 'string', 'max:191'],
            'delivery_address.city' => ['required_if:delivery_address.customer_address_id,null', 'string', 'max:191'],
            'delivery_address.state' => ['required_if:delivery_address.customer_address_id,null', 'string', 'max:191'],
            'delivery_address.zip' => ['required_if:delivery_address.customer_address_id,null', 'string', 'max:191'],
            'delivery_address.phone' => ['required_if:delivery_address.customer_address_id,null', new PhoneRule(), 'string', 'max:191'],

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
//            'shipping_methods' => ['sometimes', 'array', new CheckDataIfAddShippingMethods($this->getOrder())],
//            'shipping_methods.*.name' => ['required_with:shipping_method', 'string', ShippingMethod::ruleIn()],
//            'shipping_methods.*.cost' => ['required_with:shipping_method', 'numeric', 'min:0'],
//            'shipping_methods.*.terms' => ['nullable', 'string'],
//            'shipping_methods.*.items_ids' => ['required_with:shipping_method', 'array'],
            'payment' => ['sometimes', 'array'],
            'payment.terms' => ['nullable', 'string', PaymentTerms::ruleIn()],
            'payment.method' => ['required_with:payment', 'string', PaymentMethod::ruleIn()],
            'payment.with_tax_exemption' => ['nullable', 'boolean'],
            'delivery_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function getDto(): OrderDto
    {
        return OrderDto::byArgs($this->validated());
    }

    public function getOrder(): Order
    {
        if(!$this->order){
            /** @var $repo OrderRepository */
            $repo = resolve(OrderRepository::class);

            /** @var $model Order */
            $this->order = $repo->getById($this->route('id'));
        }

        return $this->order;
    }

    // если заявка не в состоянии draft, то ряд полей становятся обязательными
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validationData();
            if(!$this->getOrder()->isDraft()){
                if (!array_key_exists('delivery_type', $data)) {
                    $validator->errors()->add('delivery_type', __('validation.required', ['attribute' => 'Delivery type']));
                    return;
                }
                if ($data['delivery_type'] == DeliveryType::Pickup() && !array_key_exists('billing_address', $data)) {
                    $validator->errors()->add('billing_address', __('validation.required', ['attribute' => 'Billing Address']));
                    return;
                }
                if ($data['delivery_type'] == DeliveryType::Delivery() && !array_key_exists('delivery_address', $data)) {
                    $validator->errors()->add('delivery_address', __('validation.required', ['attribute' => 'Delivery Address']));
                    return;
                }
                if (!array_key_exists('payment', $data)) {
                    $validator->errors()->add('payment', __('validation.required', ['attribute' => 'Payment']));
                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'delivery_address.first_name.required_if' => __('validation.required', ['attribute' => 'First name']),
            'delivery_address.last_name.required_if' => __('validation.required', ['attribute' => 'Last name']),
            'delivery_address.address.required_if' => __('validation.required', ['attribute' => 'Address']),
            'delivery_address.city.required_if' => __('validation.required', ['attribute' => 'City']),
            'delivery_address.state.required_if' => __('validation.required', ['attribute' => 'State']),
            'delivery_address.zip.required_if' => __('validation.required', ['attribute' => 'Zip']),
            'delivery_address.phone.required_if' => __('validation.required', ['attribute' => 'Phone']),
            'billing_address.first_name.required_with' => __('validation.required', ['attribute' => 'First name']),
            'billing_address.last_name.required_with' => __('validation.required', ['attribute' => 'Last name']),
            'billing_address.address.required_with' => __('validation.required', ['attribute' => 'Address']),
            'billing_address.city.required_with' => __('validation.required', ['attribute' => 'City']),
            'billing_address.state.required_with' => __('validation.required', ['attribute' => 'State']),
            'billing_address.zip.required_with' => __('validation.required', ['attribute' => 'Zip']),
            'billing_address.phone.required_with' => __('validation.required', ['attribute' => 'Phone']),
            'payment.method.required_with' => __('validation.required', ['attribute' => 'Payment method']),
        ];
    }
}
