<?php

namespace App\GraphQL\Mutations\Common\Avatars;

use App\GraphQL\Types\FileType;
use App\GraphQL\Types\Media\MediaType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class BaseAvatarUploadMutation extends BaseAvatarMutation
{
    public const NAME = 'AvatarUpload';

    public function args(): array
    {
        return array_merge(
            $this->avatarArgs(),
            [
                'image' => [
                    'type' => FileType::nonNullType(),
                ],
            ]
        );
    }

    public function type(): Type
    {
        return MediaType::type();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Media
    {
        $model = $this->resolveModel($args['model_type'], $args['model_id']);

        $model->uploadAvatar($args['image']);

        return $model->avatar();
    }
}
