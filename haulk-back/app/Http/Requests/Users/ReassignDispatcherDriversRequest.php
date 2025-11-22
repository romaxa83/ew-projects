<?php

namespace App\Http\Requests\Users;

use App\Rules\Users\AccessToDispatcher;
use App\Rules\Users\ExistsDriversAsDispatcher;
use App\Rules\Users\IsDispatcher;
use Illuminate\Foundation\Http\FormRequest;

class ReassignDispatcherDriversRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('orders update') && $this->user()->can('users update');
    }

    public function prepareForValidation()
    {
        $this->merge([
            'dispatcherFrom' => $this->route()->parameter('dispatcherFrom'),
            'dispatcherTo' => $this->route()->parameter('dispatcherTo')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dispatcherFrom' => [new AccessToDispatcher($this->user()), new IsDispatcher(), new ExistsDriversAsDispatcher()],
            'dispatcherTo' => [new AccessToDispatcher($this->user()), new IsDispatcher()]
        ];
    }
}
