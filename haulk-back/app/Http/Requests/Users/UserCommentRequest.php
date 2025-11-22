<?php

namespace App\Http\Requests\Users;

use Carbon\CarbonTimeZone;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Exception;

class UserCommentRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
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
