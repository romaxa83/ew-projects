<?php

namespace App\Http\Requests\Employees;

use App\Enums\Employees\Status;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request for update employees",
 *     @OA\Property(property="status", title="Status", description="", type="string",
 *          enum={"pause", "free", "talk", "registration_error"}
 *     ),
 *     required={"status"},
 * )
 */

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Status::ruleIn()],
        ];
    }
}
