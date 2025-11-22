<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords;

use App\Dto\Catalog\Products\ProductKeywordDto;
use App\GraphQL\InputTypes\Catalog\Products\ProductKeywordInput;
use App\GraphQL\Types\Catalog\Products\ProductKeywordType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductKeyword;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Services\Catalog\Products\ProductKeywordService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductKeywordUpdateMutation extends BaseMutation
{
    public const NAME = 'productKeywordUpdate';
    public const PERMISSION = UpdatePermission::KEY;

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
            'input' => [
                'type' => ProductKeywordInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return ProductKeywordType::nonNullType();
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
    ): ProductKeyword {
        return makeTransaction(
            fn() => $this->service->update(
                ProductKeyword::find($args['id']),
                ProductKeywordDto::byArgs($args['input'])
            )
        );
    }
}