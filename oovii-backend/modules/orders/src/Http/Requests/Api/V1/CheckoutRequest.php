<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhonePatterns;
use WezomCms\Orders\Models\Delivery;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Checkout request",
 *     required={"delivery_id", "delivery_data", "recipient_data"}
 * )
 */
class CheckoutRequest extends FormRequest
{
    private ?array $deliveryRules = null;

    public function rules(): array
    {
        $rules = [
            'payment_id' => 'required|integer|exists:payments,id',
            'delivery_id' => 'required|integer|exists:deliveries,id',
            'recipient' => 'required|array',
            'recipient.recipient_is_me' => 'required|bool',
            'recipient.name' => 'nullable|required_if:recipient.recipient_is_me,false|string|max:255',
            'recipient.surname' => 'nullable|required_if:recipient.recipient_is_me,false|string|max:255',
            'recipient.patronymic' => 'nullable|string|max:255',
            'recipient.phone' => [
                'nullable',
                'string',
                new PhonePatterns,
                'max:255',
                'required_if:recipient.recipient_is_me,false'
            ],
            'recipient.email' => 'nullable|string|email|max:255',
            'recipient.comment' => 'nullable|string|max:700',
        ];

        $user = Auth::user();

        if ($user) {
            $rules['use_bonus'] = ['nullable', 'integer', 'min:0', 'max:' . $user->bonus];
        }

        $delivery = Delivery::query()->find($this->get('delivery_id'));

        if ($delivery && $delivery->driver && $driver = $delivery->makeDriver()) {
            $this->deliveryRules = $driver->getValidationRules();
        }

        $rules['delivery_data'] = ['required', 'array'];

        if ($this->deliveryRules) {
            foreach ($this->deliveryRules[0] as $attribute => $rule) {
                $rules['delivery_data.' . $attribute] = $rule;
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'recipient' => __('cms-orders::site.recipient.Recipient data'),
            'recipient.recipient_is_me' => __('cms-orders::site.recipient.Recipient is me'),
            'recipient.name' => __('cms-orders::site.recipient.Name'),
            'recipient.surname' => __('cms-orders::site.recipient.Surname'),
            'recipient.patronymic' => __('cms-orders::site.recipient.Patronymic'),
            'recipient.phone' => __('cms-orders::site.recipient.Phone'),
            'recipient.email' => __('cms-orders::site.recipient.Email'),
            'recipient.comment' => __('cms-orders::site.recipient.Comment'),
            'use_bonus' => __('cms-users::site.referrals.Use bonus'),
            'delivery_id' => __('cms-orders::site.checkout.Delivery'),
            'delivery_data' => __('cms-orders::site.checkout.Delivery data'),
        ];

        if ($this->deliveryRules && count($this->deliveryRules) >= 3) {
            foreach ($this->deliveryRules[2] as $attribute => $translation) {
                $attributes['delivery_data.' . $attribute] = $translation;
            }
        }

        return $attributes;
    }

    /**
     * @OA\Property(property="payment_id", title="Payment", description="ID способа оплаты", example=1)
     * @OA\Property(property="delivery_id", title="Delivery", description="ID варианта доставки", example=2)
     * @OA\Property(property="delivery_data", type="object", title="Delivery data",
     *      @OA\Property(property="region_code", title="Region", description="Идентификатор области", example="299"),
     *      @OA\Property(property="city_code", title="City", description="Идентификатор города", example=11490),
     *      @OA\Property(property="branch_code", title="Branch", description="Идентификатор пункта выдачи", example="NSK279"),
     *      @OA\Property(property="postal_code", title="Postal code", description="Почтовый индекс", example="123456"),
     *      @OA\Property(property="address", title="Address", description="Адрес получателя", example="ул. Какая-то, 23, кв. 12"),
     *      @OA\Property(property="tariff_code", title="Tariff", description="Код тарифа службы доставки", example=139),
     *      @OA\Property(property="time", title="Time", description="Время доставки", example="09:00 - 10:00")
     * )
     * @OA\Property(property="recipient", type="object", title="Recipient data",
     *      @OA\Property(property="recipient_is_me", title="Recipient is me", description="Получатель - я", example=true),
     *      @OA\Property(property="name", title="Name", description="Имя получателя", example="Станислав"),
     *      @OA\Property(property="surname", title="Surname", description="Фамилия получателя", example="Николаенко"),
     *      @OA\Property(property="phone", title="Phone", description="Телефон получателя", example="+77052312342"),
     *      @OA\Property(property="email", title="Email", description="e-mail получателя", example="stanislav@gmail.com"),
     *      @OA\Property(property="comment", title="Comment", description="Комментарий к заказу", example="Комментарий к заказу"),
     * )
     * @OA\Property(property="use_bonus", title="Bonuses sum", description="Сумма используемых бонусов", example=150)
     */
}
