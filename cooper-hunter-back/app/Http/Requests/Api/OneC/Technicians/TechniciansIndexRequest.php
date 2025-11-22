<?php

namespace App\Http\Requests\Api\OneC\Technicians;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Technicians\TechnicianListPermission;
use JetBrains\PhpStorm\Pure;

class TechniciansIndexRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(TechnicianListPermission::KEY);
    }

    #[Pure] public function rules(): array
    {
        return $this->getPaginationRules();
    }
}
