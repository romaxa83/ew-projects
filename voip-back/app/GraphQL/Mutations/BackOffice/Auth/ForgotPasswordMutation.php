<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Auth;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Notifications\Auth\ForgotPasswordVerificationNotification;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\VerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;

class ForgotPasswordMutation extends BaseMutation
{
    public const NAME = 'ForgotPassword';
    public const DESCRIPTION = 'Метод для отправки ссылки для сброса пароля. На почту клиента приходит ссылка в виде {link}/{token}, {link}.';

    public function __construct(
        protected AdminRepository $adminRepository,
        protected EmployeeRepository $employeeRepository,
        protected VerificationService $verificationService
    )
    {}

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
        /** @var $user Admin|Employee */
        $user = $this->employeeRepository->getBy('email', $args['email']);
        if(!$user){
            $user = $this->adminRepository->getBy('email', $args['email']);
        }

        Notification::route('mail', $user->email->getValue())
            ->notify(
                (new ForgotPasswordVerificationNotification(
                    $user,
                    $this->verificationService->getLinkForPasswordReset($user)
                ))->locale(app()->getLocale())
            );

        return true;
    }

    protected function rules(array $args = []): array
    {
        if ($this->adminRepository->existBy(['email' => $args['email']])) {
            $this->setAdminGuard();
            $rule = 'exists:'.Admin::TABLE.',email';
        } else {
            $this->setEmployeeGuard();
            $rule = 'exists:'.Employee::TABLE.',email';
        }

        return [
            'email' => 'required|email:filter|' . $rule,
        ];
    }
}
