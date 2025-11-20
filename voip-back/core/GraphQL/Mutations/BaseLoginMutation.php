<?php

namespace Core\GraphQL\Mutations;

use App\Models\Employees\Employee;
use App\Rules\LoginAdmin;
use App\Services\Employees\EmployeeService;
use App\Traits\Model\LoginDataTrait;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Services\Auth\AuthPassportService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseLoginMutation extends BaseMutation
{
    use LoginDataTrait;

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->getAuthGuard()->guest();
    }

    public function getAuthorizationMessage(): string
    {
        return AuthorizationMessageEnum::AUTHORIZED;
    }

    public function args(): array
    {
        return [
            'username' => Type::nonNull(Type::string()),
            'password' => Type::nonNull(Type::string()),
        ];
    }

    abstract protected function getPassportService(): AuthPassportService;

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array
    {
        $tokens = $this->getPassportService()->auth($args['username'], $args['password']) + [
            'guard' => $this->guard];

        $model = $this->createLoginRecordByEmail($args['username'], $this->guard);

        if($model instanceof Employee){
            /** @var $service EmployeeService */
            $service = resolve(EmployeeService::class);
            $service->checkSipToLocation($model);
        }

        return $tokens;
    }

    protected function rules(array $args = []): array
    {
        return [
            'username' => ['required', 'email:filter'],
            'password' => ['required', 'string', 'min:8', new LoginAdmin($args)],
        ];
    }
}

