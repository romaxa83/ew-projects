<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\UploadType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Services\Users\UserService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UserAvatarUploadMutation extends BaseMutation
{
    public const NAME = 'userAvatarUpload';

    public function __construct(protected UserService $service)
    {
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->check();
    }

    public function type(): Type
    {
        return UserType::nonNullType();
    }

    public function args(): array
    {
        return [
            'file' => UploadType::nonNullType(),
        ];
    }

    public function rules(array $args = []): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimetypes:' . implode(',', config('uploads.avatar.allowed_formats')),
                'max:' . config('uploads.avatar.max_file_size_kb'),
            ]
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): User
    {
        $user = $this->getAuthGuard()->user();
        $this->service->uploadAvatar(
            $args['file'],
            $user
        );

        return $user->refresh();
    }
}
