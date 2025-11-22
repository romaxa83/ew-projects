<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\FrontOffice\Members;

use App\GraphQL\Types\NonNullType;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Notifications\Members\MemberResetPasswordVerification;
use App\Rules\Dealers\DealerResetPasswordRule;
use App\Rules\PasswordRule;
use App\Rules\Technicians\TechnicianResetPasswordRule;
use App\Rules\Users\ResetPasswordRule;
use App\Services\Dealers\DealerService;
use App\Services\Dealers\DealerVerificationService;
use App\Services\Technicians\TechnicianService;
use App\Services\Technicians\TechnicianVerificationService;
use App\Services\Users\UserService;
use App\Services\Users\UserVerificationService;
use App\Traits\Auth\EmailCryptToken;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

use function makeTransaction;

class MemberResetPasswordMutation extends BaseMutation
{
    use EmailCryptToken;

    public const NAME = 'memberResetPassword';
    public const DESCRIPTION = 'Метод принимает токен, ранее отправленный на почту в виде ссылки на фронт.';

    public function args(): array
    {
        return [
            'token' => [
                'type' => NonNullType::string(),
            ],
            'password' => Type::nonNull(Type::string()),
            'password_confirmation' => Type::nonNull(Type::string()),
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
            if ($this->guard === Technician::GUARD) {
                $userService = resolve(TechnicianService::class);
                $verificationService = resolve(TechnicianVerificationService::class);

                $decrypt = $verificationService->decryptEmailToken($args['token']);
                $user = Technician::query()->where('id', $decrypt->id)->firstOrFail();

                $this->setTechnicianGuard();
            } elseif ($this->guard === Dealer::GUARD){
                $userService = resolve(DealerService::class);
                $verificationService = resolve(DealerVerificationService::class);

                $decrypt = $verificationService->decryptEmailToken($args['token']);
                $user = Dealer::query()->where('id', $decrypt->id)->firstOrFail();

                $this->setDealerGuard();
            } else {
                $userService = resolve(UserService::class);
                $verificationService = resolve(UserVerificationService::class);

                $decrypt = $verificationService->decryptEmailToken($args['token']);
                $user = User::query()->where('id', $decrypt->id)->firstOrFail();

                $this->setUserGuard();
            }

            $newPassword = $args['password'];

            $userService->changePassword($user, $newPassword);

            $verificationService->cleanEmailVerificationCode($user);

            Notification::route('mail', $user->getEmailString())
                ->notify(
                    (new MemberResetPasswordVerification(
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
        try {
            $decrypt = $this->decryptEmailToken($args['token']);

            if (Technician::query()->where('email_verification_code', $decrypt->code)->exists()) {
                $this->setTechnicianGuard();
                $rule = new TechnicianResetPasswordRule();
            } elseif (Dealer::query()->where('email_verification_code', $decrypt->code)->exists()) {
                $this->setDealerGuard();
                $rule = new DealerResetPasswordRule();
            } else {
                $this->setUserGuard();
                $rule = new ResetPasswordRule();
            }
        } catch (Throwable $e) {
            logger($e);

            throw new TranslatedException(__('validation.custom.reset_password.code'));
        }

        return [
            'token' => ['required', 'string', $rule],
            'password' => ['required', 'string', new PasswordRule(), 'confirmed'],
        ];
    }
}
