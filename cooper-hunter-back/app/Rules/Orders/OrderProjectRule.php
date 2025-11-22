<?php

namespace App\Rules\Orders;

use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class OrderProjectRule implements Rule
{

    public function passes($attribute, $value): bool
    {
        /**@var Technician $user*/
        $user = Auth::user();

        if ($user) {
            return false;
        }

        $project = Project::whereMember($user)->find($value);

        if (!$project) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __('validation.custom.project.forbidden');
    }
}
