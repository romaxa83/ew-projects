<?php

namespace App\Http\Resources\API\Profile;

use App\Http\Resources\API\Employees\EmployeeResource;
use App\Http\Resources\API\Employees\Foremans\ForemanResource;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'employee' => EmployeeResource::make($this->employee),
        ];

        if($this->isForeman()){
            $data['foreman'] = ForemanResource::make($this->employee);
        }

        return $data;
    }
}
