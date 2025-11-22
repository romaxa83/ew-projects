<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Media;

use App\GraphQL\Types\Media\MediaModelsTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Media\Media;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class MediaDeleteMutation extends BaseMediaMutation
{
    public const NAME = 'mediaDelete';

    public function args(): array
    {
        return [
            'media_id' => NonNullType::id(),
            'model_type' => MediaModelsTypeEnum::nonNullType(),
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $media = Media::query()
            ->where('id', $args['media_id'])
            ->where('model_type', $args['model_type'])
            ->firstOrFail();

        return $media->delete();
    }
}
