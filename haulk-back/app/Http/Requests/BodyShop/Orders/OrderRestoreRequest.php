<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Rules\BodyShop\Orders\HasEnoughInventoryForWholeOrder;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class OrderRestoreRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'order' => [new HasEnoughInventoryForWholeOrder()],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge(['order' => $this->order]);
    }
}
