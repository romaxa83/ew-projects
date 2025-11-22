<?php

namespace App\GraphQL\Mutations\FrontOffice\Members;

use App\GraphQL\Types\NonNullType;
use App\Rules\MatchOldPassword;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class MemberCheckPasswordMutation extends BaseMutation
{
    public const NAME = 'checkCurrentPassword';

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'password' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return (new MatchOldPassword($this->guard))
            ->passes('password', $args['password']);
    }

    protected function rules(array $args = []): array
    {
        return [
            'password' => ['required', 'string', new MatchOldPassword($this->guard)]
        ];
    }
}
