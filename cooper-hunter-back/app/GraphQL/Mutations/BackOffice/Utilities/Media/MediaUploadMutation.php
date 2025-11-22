<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Media;

use App\Contracts\Media\HasMedia;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\Media\MediaModelsTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\BaseModel;
use Core\Exceptions\TranslatedException;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MediaUploadMutation extends BaseMediaMutation
{
    public const NAME = 'mediaUpload';

    public function args(): array
    {
        return [
            'model_id' => NonNullType::id(),
            'model_type' => MediaModelsTypeEnum::nonNullType(),
            'media' => NonNullType::listOf(FileType::nonNullType())
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        if (!$model = Relation::getMorphedModel($type = $args['model_type'])) {
            throw new TranslatedException(
                sprintf('"%s" does not support uploading images', $type)
            );
        }

        /** @var BaseModel|HasMedia $model */
        $model = $model::query()->findOrFail($args['model_id']);

        $sort = 0;
        if($last = $model->media->last()){
            $sort = $last->sort + 1;
        }

        foreach ($args['media'] ?? [] as $image) {
            $model->addMedia($image)
                ->withAttributes(['sort' => $sort++])
                ->toMediaCollection($model->getMediaCollectionName())
            ;
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
