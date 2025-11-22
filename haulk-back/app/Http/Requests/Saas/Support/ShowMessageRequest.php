<?php

namespace App\Http\Requests\Saas\Support;

use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
use App\Permissions\Saas\Support\SupportRequestShow;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ShowMessageRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('support-requests read') || $this->user()->can(SupportRequestShow::KEY);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'supportRequestMessage' => $this->route()->parameter('supportRequestMessage')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        /**@var SupportRequest $supportRequest*/
        $supportRequest = $this->route()->parameter('supportRequest');

        return [
            'supportRequestMessage' => [
                function ($attribute, $value, $fail) use ($supportRequest) {
                    /**@var SupportRequestMessage $value*/
                    if ($value->support_request_id === $supportRequest->id) {
                        return;
                    }

                    $fail(trans('Message not found.'));
                }
            ]
        ];
    }
}
