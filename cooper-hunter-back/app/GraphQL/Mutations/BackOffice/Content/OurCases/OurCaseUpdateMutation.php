<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCases;

use App\Dto\Content\OurCases\OurCaseDto;
use App\GraphQL\InputTypes\Content\OurCases\OurCaseUpdateInput;
use App\Models\Content\OurCases\OurCase;
use App\Permissions\Content\OurCases\OurCaseUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseUpdateMutation extends BaseOurCaseMutation
{
    public const NAME = 'ourCaseUpdate';
    public const PERMISSION = OurCaseUpdatePermission::KEY;

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
            fn() => $this->service->update(
                OurCase::query()
                    ->findOrFail($args['our_case']['id']),
                OurCaseDto::byArgs($args['our_case'])
            )
        );
    }

    protected function getInputType(): Type
    {
        return OurCaseUpdateInput::nonNullType();
    }
}
