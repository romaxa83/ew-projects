<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\AdminUserLoginType;
use App\Models\Users\User;
use App\Permissions\Admins\AdminLoginAsUserPermission;
use App\Services\Auth\UserPassportService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Laravel\Passport\Passport;
use Rebing\GraphQL\Support\SelectFields;

class AdminLoginAsUserMutation extends BaseMutation
{
    public const NAME = 'adminLoginAsUser';
    public const PERMISSION = AdminLoginAsUserPermission::KEY;

    public function __construct(protected UserPassportService $passportService)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'user_id' => NonNullType::id()
        ];
    }

    public function type(): Type
    {
        return AdminUserLoginType::type();
    }

    public function doResolve($root, $args, $context, ResolveInfo $info, SelectFields $fields): array
    {
        $user = User::query()->findOrFail($args['user_id']);
        $token = $user->createToken(User::GUARD);

        return [
            'access_token' => $token->accessToken,
            'expires_in' => $token->token->expires_at?->getTimestamp(),
            'token_type' => 'Bearer'
        ];
    }

    protected function rules(array $args = []): array
    {
        return [
            'user_id' => ['required', 'int', 'exists:users,id']
        ];
    }
}
