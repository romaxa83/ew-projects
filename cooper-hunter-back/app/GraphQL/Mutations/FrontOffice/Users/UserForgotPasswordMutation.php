<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Notifications\Members\MemberForgotPasswordVerification;
use App\Services\Users\UserVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;

class UserForgotPasswordMutation extends BaseMutation
{
    public const NAME = 'userForgotPasswordMutation';
    public const DESCRIPTION = 'Метод для отправки ссылки для сброса пароля. На почту клиента приходит ссылка в виде {link}/{token}, {link}.';

    public function __construct(protected UserVerificationService $userVerificationService)
    {
        $this->setUserGuard();
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
        $user = User::query()
            ->where('email', $args['email'])
            ->firstOrFail();

        Notification::route('mail', $user->getEmailString())
            ->notify(
                (new MemberForgotPasswordVerification(
                    $user,
                    $this->userVerificationService->getLinkForEmailReset($user)
                ))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    protected function rules(array $args = []): array
    {
        return [
            'email' => 'required|email:filter|exists:users,email',
        ];
    }
}
