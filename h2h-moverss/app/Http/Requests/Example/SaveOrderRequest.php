<?php

namespace App\Http\Requests\Example;

use App\Http\Requests\JsonRequest;
use App\Models\Market\MarketOrders;
use App\User;
use Auth,
    Lang;

class SaveOrderRequest extends JsonRequest
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
            'status' => 'required|in:'.implode(',', array_keys(Lang::get('order_status'))),
            'payment_status' => 'required|in:'.implode(',', array_keys(Lang::get('payment_status'))),
            'payment_type' => 'required|in:'.implode(',', array_keys(Lang::get('payment_type'))),
            'info' => 'max:255',
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:80',
            'phone' => 'required|digits:12',
            'city' => 'sometimes|min:2|max:50',
            'address' => 'sometimes|min:2|max:200',
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['info'] = strip_tags($input['info'], '<br/>');
        $input['name'] = strip_tags($input['name']);
        $input['phone'] = preg_replace('/[^0-9]/', '', $input['phone']);
        $input['city'] = strip_tags($input['city']);
        $input['address'] = strip_tags($input['address']);

        $this->replace($input);
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $project_id = request()->route('project_id');
            $order_id = request()->route('order_id');

            if (!User::find(Auth::id())->projects()->where('projects.id', $project_id)->first()) {
                $validator->errors()->add('projects_id', 'Проект не найден. Или ты слишком хитрый...');
            }

            $martet_orders = new MarketOrders();
            $martet_orders->setUserDB($project_id);

            if (!$martet_orders->find($order_id)) {
                $validator->errors()->add('order_id', 'Заказ не найден');
            }
        });
    }
}
