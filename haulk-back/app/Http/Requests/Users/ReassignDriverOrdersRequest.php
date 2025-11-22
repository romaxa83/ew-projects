<?php

namespace App\Http\Requests\Users;

use App\Rules\Users\DispatcherAccessToDriver;
use App\Rules\Users\DriverHasActiveOrders;
use App\Rules\Users\IsDriver;
use Illuminate\Foundation\Http\FormRequest;

class ReassignDriverOrdersRequest extends FormRequest
{

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('orders update');
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'driverTo' => $this->route()->parameter('driverTo'),
            'driverFrom' => $this->route()->parameter('driverFrom')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $owner = $this->user();
        return [
            'driverFrom' => [new DispatcherAccessToDriver($owner), new IsDriver(), new DriverHasActiveOrders()],
            'driverTo' => [new DispatcherAccessToDriver($owner), new IsDriver()]
        ];
    }

}
