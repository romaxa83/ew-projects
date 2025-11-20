<?php

namespace App\Http\Resources\Calls;

use App\Enums\Formats\DatetimeEnum;
use App\Http\Resources\Departments\DepartmentResource;
use App\Http\Resources\Employees\EmployeeSimpleResource;
use App\Models\Calls\Queue;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="QueueResource",
 *     @OA\Property(property="id", type="string", example=1),
 *     @OA\Property(property="caller_name", type="string", example="John Doe", description="Caller's name"),
 *     @OA\Property(property="caller_number", type="string", example="+17863783088", description="Caller's phone"),
 *     @OA\Property(property="connected_name", type="string", example="Jack Daniels",
 *      description="The name of the person receiving the call"),
 *     @OA\Property(property="connected_number", type="string", example="350",
 *      description="The name of the person receiving the call"),
 *     @OA\Property(property="wait", type="int", example=12, description="Number of seconds to wait"),
 *     @OA\Property(property="status", type="string", example="free",
 *         description="Call status, expected values: wait/connection/talk"
 *     ),
 *     @OA\Property(property="serial_number", type="string", example="540H704720431090160475"),
 *     @OA\Property(property="case_id", type="string", example="10311"),
 *     @OA\Property(property="comment", type="string", example="some comment"),
 *     @OA\Property(property="connected_at", type="string", example="2023-06-13 14:10:19", description="Format - Y-m-d H:i:s, timezone - UTC"),
 *     @OA\Property(property="called_at", type="string", example="2023-06-13 14:10:19", description="Format - Y-m-d H:i:s, timezone - UTC"),
 *     @OA\Property(property="type", type="string", example="queue",
 *         description="Call type, expected values: queue(telephone conversation in a queue)/dial(telephone conversation out of queue)"),
 *     @OA\Property(property="employee", type="object", description="Entity with the employee who received the call" ,
 *         ref="#/components/schemas/EmployeeSimpleResource"
 *     ),
 *     @OA\Property(property="department", type="object", description="The entity of the department where the call was received" ,
 *         ref="#/components/schemas/DepartmentResource"
 *     )
 * )
 */
class QueueResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Queue $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'caller_name' => $model->caller_name,
            'caller_number' => $model->caller_num,
            'connected_name' => $model->connected_name,
            'connected_number' => $model->connected_num,
            'wait' => $model->wait,
            'status' => $model->status,
            'serial_number' => $model->serial_number,
            'case_id' => $model->case_id,
            'comment' => $model->comment,
            'connected_at' => $model->connected_at?->format(DatetimeEnum::DEFAULT_FORMAT),
            'called_at' => $model->called_at?->format(DatetimeEnum::DEFAULT_FORMAT),
            'type' => $model->type,
            'employee' => EmployeeSimpleResource::make($model->employee),
            'department' => DepartmentResource::make($model->department),
        ];
    }
}
