<?php

namespace App\Http\Requests\Orders;

use App\Documents\Filters\OrderDocumentFilter;
use App\Dto\Orders\OrderIndexDto;
use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderIndexRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewList', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'state' => [
                'nullable',
                'array'
            ],
            'state.*' => [
                'required',
                'string',
                Rule::in([
                    Order::CALCULATED_STATUS_NEW,
                    Order::CALCULATED_STATUS_ASSIGNED,
                    Order::CALCULATED_STATUS_PICKED_UP,
                    Order::CALCULATED_STATUS_DELIVERED,
                    Order::CALCULATED_STATUS_DELETED,
                ])
            ],
            'dashboard_filter' => [
                'nullable',
                'string',
                Rule::in(OrderDocumentFilter::DASHBOARD)
            ],
            'has_review' => [
                'nullable'
            ],
            'has_broker_fee' => [
                'nullable'
            ],
            's' => [
                'nullable',
                'string'
            ],
            'make' => [
                'nullable',
                'string'
            ],
            'model' => [
                'nullable',
                'string'
            ],
            'year' => [
                'nullable',
                'string',
                'max:4'
            ],
            'driver_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')
            ],
            'dispatcher_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')
            ],
            'attributes' => [
                'nullable',
                'array'
            ],
            'attributes.*' => [
                'required',
                'string',
                Rule::in(OrderDocumentFilter::ATTRIBUTES)
            ],
            'invoice_id' => [
                'nullable',
                'string'
            ],
            'check_id' => [
                'nullable',
                'string'
            ],
            'company_name' => [
                'nullable',
                'string'
            ],
            'date_from' => [
                'nullable',
                'date'
            ],
            'date_to' => [
                'nullable',
                'date'
            ],
            'date_type' => [
                'nullable',
                Rule::in([
                    Order::LOCATION_PICKUP,
                    Order::LOCATION_DELIVERY,
                    Order::INVOICE_SENT,
                    Order::CREATED_AT,
                    'paid_at'
                ]),
                'required_with:date_to,date_from'
            ],
            'page' => [
                'nullable',
                'integer'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'tag_id' => [
                'nullable',
                'integer',
                Rule::exists(Tag::class, 'id')
                    ->where('type', Tag::TYPE_ORDER),
            ],
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
        ];
    }

    private function orderByIn(): string
    {
        $orderColumns = ['last_payment_stage', 'current_due', 'past_due', 'total_due'];

        return 'in:' . implode(',', $orderColumns);
    }

    private function orderTypeIn(): string
    {
        $orderTypes = ['asc', 'desc'];

        return 'in:' . implode(',', $orderTypes);
    }

    public function dto(): OrderIndexDto
    {
        return OrderIndexDto::create($this->validated());
    }
}
