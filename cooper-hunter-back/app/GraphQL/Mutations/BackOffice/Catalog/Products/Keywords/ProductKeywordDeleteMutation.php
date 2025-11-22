<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductKeyword;
use App\Permissions\Catalog\Products\DeletePermission;
use App\Services\Catalog\Products\ProductKeywordService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductKeywordDeleteMutation extends BaseMutation
{
    public const NAME = 'productKeywordDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(private ProductKeywordService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(ProductKeyword::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool {
        return makeTransaction(
            fn() => $this->service->delete(
                ProductKeyword::find($args['id'])
            )
        );
    }
}