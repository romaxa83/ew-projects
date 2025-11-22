<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Notifications\Admins\AdminForgotPasswordNotification;
use App\Services\Admins\AdminVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminForgotPasswordMutation extends BaseMutation
{
    public const NAME = 'adminForgotPassword';
    public const DESCRIPTION = 'Метод для отправки ссылки для сброса пароля. На почту клиента приходит ссылка в виде {link}/{token}, {link}.';

    public function __construct(protected AdminVerificationService $adminVerificationService)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'email' => [
                'type' => NonNullType::string(),
            ],
            'link' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->guest();
    }

    /**
     * @throws Exception
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $admin = Admin::query()
            ->where('email', $args['email'])
            ->firstOrFail();

        Notification::route('mail', $admin->getEmailString())
            ->notify(
                (new AdminForgotPasswordNotification(
                    $admin,
                    $this->adminVerificationService->getLinkForEmailReset($admin, $args['link'])
                ))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    protected function rules(array $args = []): array
    {
        return [
            'email' => ['required', 'email:filter', Rule::exists(Admin::TABLE, 'email')],
            'link' => ['required', 'url'],
        ];
    }
}
