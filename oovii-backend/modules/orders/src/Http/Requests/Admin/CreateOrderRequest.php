<?php

namespace WezomCms\Orders\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Orders\Models\Delivery;

class CreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'status_id' => 'required|exists:order_statuses,id',
            'payment_id' => 'required|exists:payments,id',
            'payed' => 'required|bool',
            'delivery_id' => 'required|int|exists:deliveries,id',
            'provider_id' => 'required|int|exists:providers,id',

            'client.surname' => 'nullable|string|max:255',
            'client.name' => 'nullable|string|max:255',
            'client.patronymic' => 'nullable|string|max:255',
            'client.email' => 'nullable|email|max:255',
            'client.phone' => 'nullable|string|max:255',

            'recipient.recipient_is_me' => 'required|bool',
            'recipient.surname' => 'nullable|required_if:recipient.recipient_is_me,0|string|max:255',
            'recipient.name' => 'nullable|required_if:recipient.recipient_is_me,0|string|max:255',
            'recipient.patronymic' => 'nullable|string|max:255',
            'recipient.phone' => 'nullable|required_if:recipient.recipient_is_me,0|string|max:255',
            'recipient.email' => 'nullable|string|email|max:255',
            'recipient.comment' => 'nullable|string|max:700',

            'deliveryInformation.region_code' => 'nullable|max:255',
            'deliveryInformation.city_code' => 'nullable|max:255',
            'deliveryInformation.branch_code' => 'nullable|max:255',
            'deliveryInformation.postal_code' => 'nullable|numeric|min:0|max:999999',
            'deliveryInformation.address' => 'nullable|max:255',
            'deliveryInformation.city' => 'nullable|max:255',
            'deliveryInformation.street' => 'nullable|string|max:50',
            'deliveryInformation.house' => 'nullable|string|max:10',
            'deliveryInformation.room' => 'nullable|int|min:1',
            'deliveryInformation.ttn' => 'nullable|string|max:100',
        ];

        // Add delivery rules
        $delivery = Delivery::published()->find($this->get('delivery_id'));

        if ($delivery && $driver = $delivery->makeDriver($this->get('deliveryInformation', []))) {
            [$dataRules, $dataMessages, $dataAttributes] = $driver->getValidationRules();

            foreach ($dataRules as $field => $rule) {
                $rules['deliveryInformation.' . $field] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'status_id' => __('cms-orders::admin.orders.Status'),
            'payment_id' => __('cms-orders::admin.orders.Payment method'),
            'payed' => __('cms-orders::admin.orders.Payed'),
            'delivery_id' => __('cms-orders::admin.orders.Delivery method'),
            'provider_id' => __('cms-orders::admin.orders.Provider'),

            'client.surname' => __('cms-orders::admin.orders.Surname'),
            'client.name' => __('cms-orders::admin.orders.Name'),
            'client.patronymic' => __('cms-orders::admin.orders.Patronymic'),
            'client.email' => __('cms-orders::admin.orders.Email'),
            'client.phone' => __('cms-orders::admin.orders.Phone'),

            'recipient.recipient_is_me' => __('cms-orders::admin.orders.Recipient is me'),
            'recipient.surname' => __('cms-orders::admin.orders.Surname'),
            'recipient.name' => __('cms-orders::admin.orders.Name'),
            'recipient.patronymic' => __('cms-orders::admin.orders.Patronymic'),
            'recipient.phone' => __('cms-orders::admin.orders.Phone'),
            'recipient.email' => __('cms-orders::admin.orders.Email'),
            'recipient.comment' => __('cms-orders::admin.orders.Comment'),

            'deliveryInformation.region_ref' => __('cms-orders::admin.courier.Region'),
            'deliveryInformation.city_ref' => __('cms-orders::admin.pickup.City'),
            'deliveryInformation.branch_ref' => __('cms-orders::admin.pickup.Branch'),
            'deliveryInformation.postal_code' => __('cms-orders::admin.courier.Postal code'),
            'deliveryInformation.tariff_code' => __('cms-orders::admin.courier.Tariff'),
            'deliveryInformation.address' => __('cms-orders::admin.courier.Address'),
            'deliveryInformation.city' => __('cms-orders::admin.courier.City'),
            'deliveryInformation.street' => __('cms-orders::admin.courier.Street'),
            'deliveryInformation.house' => __('cms-orders::admin.courier.House'),
            'deliveryInformation.room' => __('cms-orders::admin.courier.Room'),
            'deliveryInformation.ttn' => __('cms-orders::admin.orders.TTN'),
        ];
    }
}
