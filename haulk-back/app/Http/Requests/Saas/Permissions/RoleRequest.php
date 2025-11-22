<?php

namespace App\Http\Requests\Saas\Permissions;

use App\Dto\Permissions\RoleDto;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Rules\PermissionValidator;

/**
 * @property string name
 * @property string[] permissions
 */
class RoleRequest extends BaseSassRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', new PermissionValidator($this->user()::GUARD)],
        ];
    }

    public function getDto(): RoleDto
    {
        return RoleDto::fromArgs($this->validated());
    }
}
