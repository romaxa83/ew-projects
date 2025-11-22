<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;

class DriverDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            Order::DRIVER_DOCUMENTS_FIELD_NAME => [
                'required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,xls,xlsx'
            ]
        ];
    }
}
