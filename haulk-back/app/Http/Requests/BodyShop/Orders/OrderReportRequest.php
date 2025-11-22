<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $per_page
 */
class OrderReportRequest extends FormRequest
{
    private const PER_PAGE = 12;

    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setDefaults();

        return [
            'q' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['required', 'string', $this->statusIn()],
            'payment_statuses' => ['nullable', 'array'],
            'payment_statuses.*' => ['required', 'string', $this->paymentStatusIn()],
            'implementation_date_from' => ['nullable', 'date'],
            'implementation_date_to' => ['nullable', 'date'],
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }

    protected function statusIn(): string
    {
        return 'in:' . implode(',', Order::STATUSES);
    }

    protected function paymentStatusIn(): string
    {
        return 'in:' . implode(',', Order::PAYMENT_STATUSES);
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }

    public function sortableAttributes(): array
    {
        return ['current_due', 'past_due', 'total_due'];
    }
}
