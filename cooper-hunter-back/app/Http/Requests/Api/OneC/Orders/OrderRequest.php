<?php

namespace App\Http\Requests\Api\OneC\Orders;

use App\Dto\Orders\OrderDto;
use App\Enums\Orders\OrderStatusEnum;
use App\Http\Requests\BaseFormRequest;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Rules\NameRule;
use App\Rules\Orders\OrderPartRule;
use App\Rules\PhoneRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

/**
 * @bodyParam delivery_type int required The id of the DeliveryType
 */
class OrderRequest extends BaseFormRequest
{
    public const PERMISSION = OrderUpdatePermission::KEY;

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', OrderStatusEnum::ruleIn()],
            'parts.*' => [new OrderPartRule('order_category_guid', 'guid')],
            'parts.*.order_category_guid' => ['required'],
            'parts.*.quantity' => ['sometimes', 'nullable', 'int', 'min:1'],
            'parts.*.description' => ['nullable', 'string',],
            'parts.*.price' => ['nullable', 'numeric'],
            'comment' => ['nullable', 'string'],
            'first_name' => ['required', 'string', new NameRule()],
            'last_name' => ['required', 'string', new NameRule()],
            'phone' => ['required', 'string', new PhoneRule()],
            'address_first_line' => ['required', 'string'],
            'address_second_line' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'country_code' => ['required', 'string', Rule::exists(Country::class, 'country_code')],
            'state_id' => ['required', Rule::exists(State::class, 'id')],
            'zip' => ['required', 'string'],
            'delivery_type' => [
                'required',
                'int',
                Rule::exists(OrderDeliveryType::class, 'id')
                    ->where('active', true)
            ],
            'trk_number' => ['nullable', 'string'],
            'payment' => ['required', 'array'],
            'payment.order_price' => ['required', 'numeric'],
            'payment.order_price_with_discount' => ['required', 'numeric'],
            'payment.shipping_cost' => ['required', 'numeric'],
            'payment.tax' => ['required', 'numeric'],
            'payment.discount' => ['required', 'numeric'],
            'payment.paid_at' => ['nullable', 'int'],
        ];
    }

    public function getDto(): OrderDto
    {
        return OrderDto::byArgs($this->transformData());
    }

    private function transformData(): array
    {
        $order = $this->getOrder();

        $data = $this->validated();

        // not sure that 1c wants to change this data
        $data['technician_id'] = $order->technician_id;
        $data['project_id'] = $order->project_id;
        $data['product_id'] = $order->product_id;
        $data['serial_number'] = $order->serial_number;
        $data['payment']['paid_at'] = $order->payment?->paid_at;

        $parts = OrderCategory::query()
            ->whereIn('guid', array_column($data['parts'], 'order_category_guid'))
            ->get()
            ->keyBy('guid');

        foreach ($data['parts'] as &$part) {
            $part['id'] = $parts->get($part['order_category_guid'])->id;
            unset($part['order_category_id']);
        }

        unset($part);

        return $data;
    }

    private function getOrder(): Order
    {
        if (
            ($order = $this->order)
            && ($order instanceof Order)
            && ($order->exists)
        ) {
            return $order;
        }

        throw (new ModelNotFoundException())->setModel(Order::class);
    }
}
