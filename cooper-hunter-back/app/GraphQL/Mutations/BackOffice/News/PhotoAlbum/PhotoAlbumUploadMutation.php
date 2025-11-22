<?php

namespace App\GraphQL\Mutations\BackOffice\News\PhotoAlbum;

use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\PhotoAlbum;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class PhotoAlbumUploadMutation extends BasePhotoAlbumMutation
{
    public const NAME = 'photoUpload';

    public function args(): array
    {
        return [
            'media' => NonNullType::listOf(FileType::nonNullType())
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $album = PhotoAlbum::query()->firstOrCreate();

        foreach ($args['media'] ?? [] as $image) {
            $album->addMedia($image)
                ->toMediaCollection($album->getMediaCollectionName());
        }

        return true;
    }

    protected function rules(array $args = []): array
    {
        return [
            'media' => ['required', 'array'],
            'media.*' => ['required', 'file'],
        ];
    }
}
