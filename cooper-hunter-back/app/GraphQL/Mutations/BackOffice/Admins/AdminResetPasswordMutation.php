<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Notifications\Admins\AdminResetPasswordNotification;
use App\Rules\Admins\AdminResetPasswordRule;
use App\Services\Admins\AdminService;
use App\Services\Admins\AdminVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminResetPasswordMutation extends BaseMutation
{
    public const NAME = 'adminResetPassword';
    public const DESCRIPTION = 'Метод принимает токен, ранее отправленный на почту в виде ссылки на фронт.';

    public function __construct(
        protected AdminVerificationService $adminVerificationService,
        protected AdminService $adminService
    ) {
    }

    public function args(): array
    {
        return [
            'token' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->guest();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(function () use ($args) {
            $decrypt = $this->adminVerificationService->decryptEmailToken($args['token']);

            $admin = Admin::query()->where('id', $decrypt->id)->firstOrFail();

            $newPassword = $this->adminService->createNewPassword();

            $this->adminService->changePassword($admin, $newPassword);

            $this->adminVerificationService->cleanEmailVerificationCode($admin);

            Notification::route('mail', $admin->getEmailString())
                ->notify(
                    (new AdminResetPasswordNotification(
                        $admin,
                        $newPassword
                    ))
                        ->locale(app()->getLocale())
                );

            return true;
        });
    }

    protected function rules(array $args = []): array
    {
        return ['token' => ['required', 'string', new AdminResetPasswordRule()]];
    }
}
