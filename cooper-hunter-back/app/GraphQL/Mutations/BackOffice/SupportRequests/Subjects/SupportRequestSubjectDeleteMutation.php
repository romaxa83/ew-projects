<?php

namespace App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects;

use App\Dto\SupportRequests\SupportRequestSubjectDto;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectDeletePermission;
use App\Services\SupportRequests\SupportRequestSubjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SupportRequestSubjectDeleteMutation extends BaseMutation
{
    public const NAME = 'supportRequestSubjectDelete';
    public const PERMISSION = SupportRequestSubjectDeletePermission::KEY;

    public function __construct(protected SupportRequestSubjectService $service)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(
                        SupportRequestSubject::class,
                        'id'
                    )
                ]
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
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
                SupportRequestSubjectDto::byArgs($args)
            )
        );
    }
}
