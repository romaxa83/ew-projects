<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Queries\Back;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Ability;

class BackAdmins extends BackFieldResolver
{
    public function resolve(Context $context): Builder
    {
        return Admin::query()->filter($context->getArgs());
    }

    protected function rules(array $args = []): array
    {
        return [
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['nullable', 'integer', 'exists:roles,id'],
            'query' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(AdminStatusEnum::class)],
        ];
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Admin::class)->viewAction();
    }
}
