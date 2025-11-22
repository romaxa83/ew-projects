<?php

namespace App\GraphQL\Mutations\BackOffice\Localization;

use App\Dto\Localizations\TranslateDto;
use App\GraphQL\InputTypes\Localization\TranslateInputType;
use App\GraphQL\Types\Localization\TranslateType;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateCreatePermission;
use App\Services\Localizations\TranslateService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TranslateCreateMutation extends BaseMutation
{
    public const NAME = 'translateCreate';
    public const PERMISSION = TranslateCreatePermission::KEY;

    public function __construct(private TranslateService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TranslateType::nonNullType();
    }

    public function args(): array
    {
        return [
            'translate' => [
                'type' => TranslateInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Translate
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Translate {
        return makeTransaction(
            fn() => $this->service->create(
                TranslateDto::byArgs($args['translate'])
            )
        );
    }
}
