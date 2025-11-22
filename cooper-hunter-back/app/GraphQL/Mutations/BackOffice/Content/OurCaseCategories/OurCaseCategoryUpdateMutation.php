<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories;

use App\Dto\Content\OurCaseCategories\OurCaseCategoryDto;
use App\GraphQL\InputTypes\Content\OurCaseCategories\OurCaseCategoryUpdateInput;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Permissions\Content\OurCaseCategories\OurCaseCategoryUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseCategoryUpdateMutation extends BaseOurCaseCategoryMutation
{
    public const NAME = 'ourCaseCategoryUpdate';
    public const PERMISSION = OurCaseCategoryUpdatePermission::KEY;

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): OurCaseCategory {
        return makeTransaction(
            fn() => $this->service->update(
                OurCaseCategory::query()
                    ->findOrFail($args['our_case_category']['id']),
                OurCaseCategoryDto::byArgs($args['our_case_category'])
            )
        );
    }

    protected function getInputType(): Type
    {
        return OurCaseCategoryUpdateInput::nonNullType();
    }
}
