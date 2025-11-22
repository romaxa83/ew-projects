<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Dto\Users\UserDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserProfileType;
use App\Models\Users\User;
use App\Permissions\Users\UserUpdatePermission;
use App\Rules\ExistsLanguages;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Services\Users\UserService;
use App\Traits\Auth\SmsConfirmable;
use App\Traits\ValidateOnlyRequest;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserUpdateMutation extends BaseMutation
{
    use SmsConfirmable;
    use ValidateOnlyRequest;

    public const NAME = 'userUpdate';
    public const PERMISSION = UserUpdatePermission::KEY;

    public function __construct(protected UserService $service)
    {
        $this->setUserGuard();
    }

    public function type(): Type
    {
        return UserProfileType::type();
    }

    public function args(): array
    {
        return [
                'first_name' => NonNullType::string(),
                'last_name' => NonNullType::string(),
                'email' => NonNullType::string(),
                'phone' => Type::string(),
                'lang' => Type::string(),
            ]
            + $this->smsAccessTokenArg()
            + $this->validateOnlyArg();
    }

    /** @throws Throwable */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): User
    {
        return makeTransaction(
            fn() => $this->service->update(
                $this->user(),
                UserDto::byArgs($args)
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'email' => ['required', 'string', 'email:filter', MemberUniqueEmailRule::ignoreUser($this->authId())],
            'phone' => ['nullable', 'string', MemberUniquePhoneRule::ignoreUser($this->authId())],
            'lang' => ['nullable', 'string', 'min:2', 'max:3', new ExistsLanguages()]
        ];
    }
}
