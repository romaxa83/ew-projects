<?php

namespace App\Http\Resources\Employees;

use App\Http\Resources\Departments\DepartmentResource;
use App\Http\Resources\Sips\SipResource;
use App\Models\Employees\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="EmployeeSimpleResource",
 *     @OA\Property(property="id", type="string", example=1),
 *     @OA\Property(property="guid", type="string", example="06d93bc9-2d53-4368-8cf6-25404bff61f5"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="email@example"),
 *     @OA\Property(property="status", type="string", example="free",
 *         description="User status, expected values: free/pause/talk/registration_error"
 *     ),
 *     @OA\Property(property="sip", type="object", description="" ,
 *         ref="#/components/schemas/SipResource"
 *     ),
 *     @OA\Property(property="department", type="object", description="" ,
 *         ref="#/components/schemas/DepartmentResource"
 *     )
 * )
 */
class EmployeeSimpleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Employee $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'guid' => $model->guid,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'email' => $model->email->getValue(),
            'status' => $model->status,
            'sip' => SipResource::make($model->sip),
            'department' => DepartmentResource::make($model->department),
        ];
    }
}
