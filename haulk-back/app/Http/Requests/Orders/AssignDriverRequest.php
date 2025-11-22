<?php

namespace App\Http\Requests\Orders;

use App\Models\Users\User;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AssignDriverRequest extends FormRequest
{
    use ValidationRulesTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $order = $this->order;

        if (!$order || ($order && $order->ifNullDriverOrDispatcherAllowed())) {
            $driverAndDispatcherRule = 'nullable';
        } else {
            $driverAndDispatcherRule = 'required';
        }

        return [
            'dispatcher_id' => [
                $driverAndDispatcherRule,
                'required_with:driver_id',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (
                        !$user || !in_array(
                            $user->getRoleName(),
                            [
                                User::SUPERADMIN_ROLE,
                                User::ADMIN_ROLE,
                                User::DISPATCHER_ROLE,
                            ]
                        )
                    ) {
                        $fail(trans('Dispatcher not found.'));
                    }
                }
            ],
            'driver_id' => [
                $driverAndDispatcherRule,
                'integer',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->isDriver()) {
                        $fail(trans('Driver not found.'));
                    }
                }
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        if (
            $this->filled('driver_id')
            && $this->filled('dispatcher_id')
        ) {
            $validator->after(
                function ($validator) {
                    if (
                        User::where(
                            [
                                ['id', $this->input('driver_id')],
                                ['owner_id', $this->input('dispatcher_id')],
                            ]
                        )->doesntExist()
                    ) {
                        $validator
                            ->errors()
                            ->add('dispatcher_id', trans('Dispatcher not found.'))
                            ->add('driver_id', trans('Driver not found.'));
                    }
                }
            );
        }
    }
}
