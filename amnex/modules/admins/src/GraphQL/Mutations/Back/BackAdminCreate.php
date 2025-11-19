<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Throwable;
use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminService;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Ability;

final class BackAdminCreate extends BackFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(
        protected AdminService $adminService,
        protected AdminVerificationService $adminVerificationService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function resolve(Context $context): Admin
    {
        $dto = $context->getDto(AdminDto::class, 'admin');

        $admin = $this->adminService->create($dto, AdminStatusEnum::PENDING);

        $this->adminVerificationService->sendSetPasswordLink($admin);

        return $admin;
    }

    protected function rules(array $args = []): array
    {
        return $this->getDtoValidationRules(AdminDto::class, $args, 'admin');
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Admin::class)->createAction();
    }
}
