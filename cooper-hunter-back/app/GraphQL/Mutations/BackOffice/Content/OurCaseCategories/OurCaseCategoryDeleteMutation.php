<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Permissions\Content\OurCaseCategories\OurCaseCategoryDeletePermission;
use App\Services\OurCases\OurCaseCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseCategoryDeleteMutation extends BaseMutation
{
    public const NAME = 'ourCaseCategoryDelete';
    public const PERMISSION = OurCaseCategoryDeletePermission::KEY;

    public function __construct(protected OurCaseCategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(OurCaseCategory::TABLE, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                OurCaseCategory::query()
                    ->findOrFail($args['id'])
            )
        );
    }
}
