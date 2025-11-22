<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\UpdatePermission;
use App\Services\Catalog\Categories\CategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class CategoryToggleActiveMutation extends BaseMutation
{
    public const NAME = 'categoryToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(protected CategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CategoryType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(Category::TABLE, 'id')
                ]
            ]
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        return $this->service->toggleActive(
            Category::find($args['id'])
        );
    }
}

