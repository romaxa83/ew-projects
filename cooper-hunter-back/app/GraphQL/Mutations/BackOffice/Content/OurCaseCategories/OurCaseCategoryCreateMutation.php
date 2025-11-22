<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories;

use App\Dto\Content\OurCaseCategories\OurCaseCategoryDto;
use App\GraphQL\InputTypes\Content\OurCaseCategories\OurCaseCategoryCreateInput;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Permissions\Content\OurCaseCategories\OurCaseCategoryCreatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseCategoryCreateMutation extends BaseOurCaseCategoryMutation
{
    public const NAME = 'ourCaseCategoryCreate';
    public const PERMISSION = OurCaseCategoryCreatePermission::KEY;

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
            fn() => $this->service->create(
                OurCaseCategoryDto::byArgs($args['our_case_category'])
            )
        );
    }

    protected function getInputType(): Type
    {
        return OurCaseCategoryCreateInput::nonNullType();
    }
}
