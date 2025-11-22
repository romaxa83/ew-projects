<?php

namespace App\GraphQL\Mutations\BackOffice\Localization;

use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateDeletePermission;
use App\Services\Localizations\TranslateService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TranslateDeleteMutation extends BaseMutation
{
    public const NAME = 'translateDelete';
    public const PERMISSION = TranslateDeletePermission::KEY;

    public function __construct(private TranslateService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Translate::class, 'id')
                ]
            ]
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->delete(
            Translate::find($args['id'])
        );
    }

}
