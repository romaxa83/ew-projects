<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Exception;
use Wezom\Admins\AdminConst;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Ability;

final class BackAdminResendEmailVerification extends BackFieldResolver
{
    protected bool $toResponseMessage = true;

    public function __construct(protected AdminVerificationService $adminVerificationService)
    {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): void
    {
        $admin = Admin::query()->findOrFail($context->getArg('id'));

        $this->adminVerificationService->sendLinkForVerificationEmail($admin);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:admins,id'],
        ];
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Admin::class)->action(AdminConst::RESEND_EMAIL_VERIFICATION);
    }
}
