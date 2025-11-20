<?php

namespace WezomCms\ServicesOrders\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\Http\Requests\Rule\ServiceTypeRule;
use WezomCms\ServicesOrders\Http\Requests\Rule\TypeRule;

class OrderRequest extends FormRequest
{
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
//        $rule = [
//            'type' => ['required', 'integer'],
//            'serviceType' => ['required_if:type,'. ServiceType::TYPE_STO .','. ServiceType::TYPE_INSURANCE],
//            'usersVehicleId' => ['nullable', 'integer', 'exists:user_cars,id'],
//            'dealerCenterId' => ['required', 'integer', 'exists:dealerships,id'],
//            'cityId' => ['nullable', 'integer'],
//            'timestamp' => ['required', 'integer'],
//            'comment' => ['nullable', 'string'],
//            'recall' => ['boolean'],
//            'mileage' => ['nullable'],
//        ];
//
//        if($this->request->get('type') == ServiceType::TYPE_TEST_DRIVE){
//            $rule['anotherVehicle'] = ['nullable', 'array'];
//            $rule['anotherVehicle.brandId'] = ['required__without:usersVehicleId', 'integer'];
//            $rule['anotherVehicle.modelId'] = ['required__without:usersVehicleId', 'integer'];
//            $rule['anotherVehicle.isFamilyCar'] = ['required__without:usersVehicleId'];
//        } else {
//            $rule['anotherVehicle'] = ['nullable', 'array'];
//            $rule['anotherVehicle.vinCode'] = ['nullable', 'string'];
//            $rule['anotherVehicle.number'] = ['required__without:usersVehicleId', 'string'];
//            $rule['anotherVehicle.brandId'] = ['required__without:usersVehicleId', 'integer'];
//            $rule['anotherVehicle.modelId'] = ['required__without:usersVehicleId', 'integer'];
//            $rule['anotherVehicle.isFamilyCar'] = ['required__without:usersVehicleId'];
//            $rule['anotherVehicle.dealerCenterId'] = ['nullable', 'integer'];
//            $rule['anotherVehicle.engineId'] = ['required__without:usersVehicleId', 'integer'];
//            $rule['anotherVehicle.engineVolume'] = ['required__without:usersVehicleId'];
//            $rule['anotherVehicle.transmissionId'] = ['required__without:usersVehicleId', 'integer'];
//        }
$rule = [];
        return $rule;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
