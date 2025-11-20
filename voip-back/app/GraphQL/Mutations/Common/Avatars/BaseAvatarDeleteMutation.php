<?php

namespace App\GraphQL\Mutations\Common\Avatars;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAvatarDeleteMutation extends BaseAvatarMutation
{
    public const NAME = 'AvatarDelete';

    public function args(): array
    {
        return $this->avatarArgs();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $this
            ->resolveModel($args['model_type'], $args['model_id'])
            ->deleteAvatar();

        return true;
    }
}
