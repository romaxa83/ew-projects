<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCases;

use App\Dto\Content\OurCases\OurCaseDto;
use App\GraphQL\InputTypes\Content\OurCases\OurCaseCreateInput;
use App\Models\Content\OurCases\OurCase;
use App\Permissions\Content\OurCases\OurCaseCreatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseCreateMutation extends BaseOurCaseMutation
{
    public const NAME = 'ourCaseCreate';
    public const PERMISSION = OurCaseCreatePermission::KEY;

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): OurCase {
        return makeTransaction(
            fn() => $this->service->create(
                OurCaseDto::byArgs($args['our_case'])
            )
        );
    }

    protected function getInputType(): Type
    {
        return OurCaseCreateInput::nonNullType();
    }
}
