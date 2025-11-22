<?php

namespace App\Http\Requests\Saas\Admins;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Admins\Admin;
use App\Rules\RoleExists;
use App\Traits\Requests\OnlyValidateForm;

/**
 * @property null|int page
 * @property null|int per_page
 * @property null|string query
 * @property array|int[] roles
 */
class AdminFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return parent::rules() + [
            'query' => ['nullable', 'string', 'min:' . config('admins.filter.min_query_length')],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', new RoleExists(Admin::GUARD)],
        ];
    }

    public function orderBy(): string
    {
        return 'in:' . implode(
                ',',
                [
                    'full_name',
                    'email',
                    'phone',
                ]
            );
    }
}
