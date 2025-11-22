<?php

namespace App\Http\Requests\Orders\BS;

use App\Dto\Orders\BS\OrderPaymentDto;
use App\Enums\Orders\PaymentMethod;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;

/**
 * @OA\Schema(type="object", title="OrderPaymentRequest",
 *     required={"amount", "payment_date", "payment_method"},
 *     @OA\Property(property="amount", type="number", example="13"),
 *     @OA\Property(property="payment_date", type="string", example="02/13/2023 10:00", description="Formst - m/d/Y"),
 *     @OA\Property(property="payment_method", type="string", example="cash",
 *         enum={"cash", "credit_card", "miney_order", "quick_pay", "cashapp", "paypal", "venmo", "zelle"}
 *     ),
 *     @OA\Property(property="notes", type="string", example="some notes", description="notes"),
 *     @OA\Property(property="reference_number", type="string", description="notes"),
 * )
 */

class OrderPaymentRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    protected Order|null $order = null;

    public function rules(): array
    {
        return  [
            'amount' => ['required', 'numeric', 'min:0', 'max:' . ($this->getOrder()->debt_amount ?? 0)],
            'payment_date' => ['required', 'date_format:m/d/Y'],
            'payment_method' => ['required', 'string', EnumHelper::ruleIn(PaymentMethod::class)],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
        ];
    }

    public function getDto(): OrderPaymentDto
    {
        return OrderPaymentDto::byArgs($this->validated());
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
}
