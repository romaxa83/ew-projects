<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Categories;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\DeletePermission;
use App\Services\Catalog\Categories\CategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CategoryDeleteMutation extends BaseMutation
{
    public const NAME = 'categoryDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(protected CategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ResponseMessageEntity
    {
        try {
            $this->service->delete(
                Category::find($args['id'])
            );

            return ResponseMessageEntity::success(__('messages.catalog.category.actions.delete.success.one-entity'));
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Category::TABLE, 'id')],
        ];
    }
}


