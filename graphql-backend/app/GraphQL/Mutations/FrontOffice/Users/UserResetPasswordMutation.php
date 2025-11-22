<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Notifications\Users\UserResetPasswordVerification;
use App\Rules\Users\ResetPasswordRule;
use App\Services\Users\UserService;
use App\Services\Users\UserVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserResetPasswordMutation extends BaseMutation
{
    public const NAME = 'userResetPassword';
    public const DESCRIPTION = 'Метод принимает токен, ранее отправленный на почту в виде ссылки на фронт.';

    public function __construct(
        protected UserVerificationService $userVerificationService,
        protected UserService $userService
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
        return make_transaction(function () use ($args) {
            $decrypt = $this->userVerificationService->decryptTokenForEmailReset($args['token']);

            $user = User::query()->where('id', $decrypt['id'])->firstOrFail();

            $newPassword = $this->userService->createNewPassword();

            $this->userService->changePassword($user, $newPassword);

            $this->userVerificationService->cleanEmailVerificationCode($user);

            Notification::route('mail', $user->getEmailString())
                ->notify(
                    (new UserResetPasswordVerification(
                        $user,
                        $newPassword
                    ))
                        ->locale(app()->getLocale())
                );

            return true;
        });
    }

    protected function rules(array $args = []): array
    {
        return ['token' => ['required', 'string', new ResetPasswordRule()]];
    }
}
