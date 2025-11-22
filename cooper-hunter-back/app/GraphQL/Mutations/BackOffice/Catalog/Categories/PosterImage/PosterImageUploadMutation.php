<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Categories\PosterImage;

use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\UpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class PosterImageUploadMutation extends BaseMutation
{
    public const NAME = 'categoryPosterImageUpload';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Category::class, 'id')],
                'description' => 'Poster can be uploaded only for root categories, otherwise error will be thrown'
            ],
            'image' => [
                'type' => FileType::nonNullType(),
                'rules' => ['image'],
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $category = Category::query()
            ->whereKey($args['id'])
            ->whereNull('parent_id')
            ->firstOrFail();

        $category->addMedia($args['image'])
            ->toMediaCollection(Category::POSTER_COLLECTION_NAME);

        return true;
    }
}
