<?php

namespace App\Http\Requests\BodyShop\Users;

use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class SearchRequest extends \App\Http\Requests\SearchRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'roles' => ['nullable', 'required_without:searchid',  'array'],
                'roles.*' => [
                    'integer',
                    'required',
                    Rule::exists(Role::TABLE, 'id')
                        ->whereIn('name', User::BS_ROLES),
                ],
            ]
        );
    }
}
