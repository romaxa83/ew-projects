<?php


namespace App\Http\Requests\Locations;


use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
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
            'name' => ['required','string','max:191'],
            'state_id' => ['required','integer','exists:states,id'],
            'zip' => ['required', 'string', 'min:3', 'max:10']
        ];
    }
}
