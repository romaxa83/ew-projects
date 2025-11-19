<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminService;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Ability;

final class BackAdminUpdate extends BackFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(
        protected AdminService $adminService,
        protected AdminVerificationService $adminVerificationService
    ) {
    }

    public function resolve(Context $context): Admin
    {
        $admin = Admin::query()->findOrFail($context->getArg('id'));

        $dto = $context->getDto(AdminDto::class, 'admin');

        $admin = $this->adminService->update($admin, $dto);

        $this->adminVerificationService->checkAndSendVerificationEmail($admin, $dto);

        return $admin;
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            [
                'id' => ['required', 'integer', 'exists:admins,id'],
            ],
            $this->getDtoValidationRules(AdminDto::class, $args, 'admin')
        );
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Admin::class)->updateAction();
    }
}
