<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Media;

use App\GraphQL\Types\NonNullType;
use App\Models\Media\Media;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class MediaSortMutation extends BaseMutation
{
    public const NAME = 'mediaSort';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'media_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => ['nullable', 'array',
                    Rule::exists(Media::class, 'id')
                ]
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        $medias = Media::query()->whereIn('id', $args['media_ids'])->get();
        try {
            foreach ($args['media_ids'] as $sort => $id){
                /** @var $item Media */
                $item = $medias->where('id', $id)->first();
                $item->update(['sort' => $sort]);
            }

            return true;
        } catch (\Throwable $e) {
            logger_info($e->getMessage());
            return false;
        }
    }
}

