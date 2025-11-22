<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Categories\PosterImage;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\UpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class PosterImageDeleteMutation extends BaseMutation
{
    public const NAME = 'categoryPosterImageDelete';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Category::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        Category::findOrFail($args['id'])
            ->clearMediaCollection(Category::POSTER_COLLECTION_NAME);

        return true;
    }
}
