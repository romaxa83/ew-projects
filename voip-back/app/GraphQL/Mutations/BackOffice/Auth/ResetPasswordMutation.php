<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Auth;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Notifications\Auth\ResetPasswordVerificationNotification;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Rules\PasswordRule;
use App\Rules\Users\ResetPasswordRule;
use App\Services\Admins\AdminService;
use App\Services\Employees\EmployeeService;
use App\Services\VerificationService;
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

class ResetPasswordMutation extends BaseMutation
{
    use EmailCryptToken;

    public const NAME = 'ResetPassword';
    public const DESCRIPTION = 'Метод принимает токен, ранее отправленный на почту в виде ссылки на фронт.';

    public function __construct(
        protected VerificationService $verificationService,
        protected AdminRepository $adminRepository,
        protected EmployeeRepository $employeeRepository,
    )
    {}

    public function args(): array
    {
        return [
            'token' => NonNullType::string(),
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
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        return makeTransaction(function () use ($args) {
            /** @var $user Admin|Employee */
            /** @var $userService AdminService|EmployeeService */
            $decrypt = $this->verificationService->decryptEmailToken($args['token']);
            if ($this->guard === Admin::GUARD) {
                $userService = resolve(AdminService::class);
                $user = $this->adminRepository->getBy('id', $decrypt->id);
                $this->setAdminGuard();
            } else {
                $userService = resolve(EmployeeService::class);
                $user = $this->employeeRepository->getBy('id', $decrypt->id);
                $this->setEmployeeGuard();
            }

            $newPassword = $args['password'];

            $userService->changePassword($user, $newPassword);

            $this->verificationService->cleanEmailVerificationCode($user);

            Notification::route('mail', $user->email->getValue())
                ->notify(
                    (new ResetPasswordVerificationNotification(
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
            if ($user = $this->adminRepository->getBy('email_verification_code', $decrypt->code)) {
                $this->setAdminGuard();
            }  else {
                $user = $this->employeeRepository->getBy('email_verification_code', $decrypt->code);
                $this->setEmployeeGuard();
            }

            $rule = new ResetPasswordRule($user);
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

