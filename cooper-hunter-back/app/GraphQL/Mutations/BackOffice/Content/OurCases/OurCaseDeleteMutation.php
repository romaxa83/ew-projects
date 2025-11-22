<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCase;
use App\Permissions\Content\OurCases\OurCaseDeletePermission;
use App\Services\OurCases\OurCaseService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OurCaseDeleteMutation extends BaseMutation
{
    public const NAME = 'ourCaseDelete';
    public const PERMISSION = OurCaseDeletePermission::KEY;

    public function __construct(protected OurCaseService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(OurCase::TABLE, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                OurCase::query()
                    ->findOrFail($args['id'])
            )
        );
    }
}
