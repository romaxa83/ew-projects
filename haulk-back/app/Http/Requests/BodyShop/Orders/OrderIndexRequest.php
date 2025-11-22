<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderIndexRequest extends FormRequest
{
    private const PER_PAGE = 10;

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('orders-bs');
    }

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
            'vehicle_year' => ['nullable', 'string', 'max:4'],
            'vehicle_make' => ['nullable', 'string'],
            'vehicle_model' => ['nullable', 'string'],
            'mechanic_id' => [
                'nullable',
                'integer',
                Rule::exists(User::TABLE_NAME, 'id'),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || $user->getRoleName() !== User::BSMECHANIC_ROLE) {
                        $fail(trans('Mechanic not found.'));
                    }
                }
            ],
            'status' => ['nullable', 'string', $this->statusIn()],
            'payment_status' => ['nullable', 'string', $this->paymentStatusIn()],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'inventory_id' => ['nullable', 'integer', Rule::exists(Inventory::TABLE_NAME, 'id')],
            'truck_id' => ['nullable', 'integer', Rule::exists(Truck::TABLE_NAME, 'id')],
            'trailer_id' => ['nullable', 'integer', Rule::exists(Trailer::TABLE_NAME, 'id')],
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
}
