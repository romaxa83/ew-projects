<?php

namespace App\Http\Requests\Orders;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UploadPdfOrderRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('orders create');
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_pdf' => ['required', 'mimes:pdf', "max:" . byte_to_kb(config('medialibrary.max_file_size'))],
        ];
    }
}
