<?php

namespace App\Http\Requests\Orders;

use Carbon\CarbonTimeZone;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Exception;

class OrderCommentRequest extends FormRequest
{
    use OnlyValidateForm;

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
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            try {
                if ($this->header('TimezoneId')) {
                    $t = CarbonTimeZone::instance($this->header('TimezoneId'));
                }
            } catch (Exception $e) {
                $validator->errors()->add('comment', trans('Invalid timezone.'));
            }
        });
    }
}
