<?php

namespace App\GraphQL\Mutations\BackOffice\News\PhotoAlbum;

use App\GraphQL\Types\NonNullType;
use App\Models\Media\Media;
use App\Models\News\PhotoAlbum;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class PhotoAlbumDeleteMutation extends BasePhotoAlbumMutation
{
    public const NAME = 'photoDelete';

    public function args(): array
    {
        return [
            'media_id' => NonNullType::id(),
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return (bool)Media::query()
            ->where('id', $args['media_id'])
            ->where('model_type', PhotoAlbum::MORPH_NAME)
            ->delete();
    }
}
