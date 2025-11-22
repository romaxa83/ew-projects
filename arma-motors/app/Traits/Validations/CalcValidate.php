<?php

namespace App\Traits\Validations;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Support\Facades\Validator;

trait CalcValidate
{
    protected function validate(array $data, Brand $brand)
    {
        $rule = [
            'brandId' => 'required',
            'modelId' => 'required',
            'engineVolumeId' => 'required',
            'mileageId' => 'required',
        ];

        if($brand->isMitsubishi()){
            $rule['driveUnitId'] = 'required';
            $rule['transmissionId'] = 'required';
            $rule['fuelId'] = 'required';
        }

        if($brand->isVolvo()){
            $rule['fuelId'] = 'required';
        }

        $validator = Validator::make($data, $rule,  $messages = [
            'required' => 'The :attribute field is required.',
        ]);

        if($validator->fails()){
            $err = current(current($validator->errors()->messages()));
            throw new \InvalidArgumentException($err, ErrorsCode::BAD_REQUEST);
        }

    }
}
