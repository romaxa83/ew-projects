<?php


namespace App\Http\Requests\Locations;



use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class StateRequest extends FormRequest
{
    use OnlyValidateForm;
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:191'],
        ];
    }
}