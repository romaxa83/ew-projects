<?php

namespace App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects;

use App\Dto\SupportRequests\SupportRequestSubjectDto;
use App\GraphQL\InputTypes\SimpleTranslationWithDescriptionInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectUpdatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\SupportRequests\SupportRequestSubjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SupportRequestSubjectUpdateMutation extends BaseMutation
{
    public const NAME = 'supportRequestSubjectUpdate';
    public const PERMISSION = SupportRequestSubjectUpdatePermission::KEY;

    public function __construct(protected SupportRequestSubjectService $service)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return SupportRequestSubjectType::nonNullType();
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
            'active' => [
                'type' => Type::boolean(),
                'rules' => [
                    'nullable',
                    'boolean'
                ],
            ],
            'translations' => [
                'type' => SimpleTranslationWithDescriptionInput::list(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator(),
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
     * @return SupportRequestSubject
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SupportRequestSubject {
        return makeTransaction(
            fn() => $this->service->update(
                SupportRequestSubjectDto::byArgs($args)
            )
        );
    }
}
