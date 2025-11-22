<?php

namespace App\GraphQL\Mutations\FrontOffice\Warranty;

use App\GraphQL\Types\NonNullType;
use App\Models\Projects\System;
use App\Rules\Projects\SystemBelongsToMemberRule;
use App\Services\Warranty\WarrantyService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class SystemCanRegisterWarrantyMutation extends BaseQuery
{
    public const NAME = 'SystemCanRegisterWarranty';

    public function __construct(protected WarrantyService $service)
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'system_id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->assertCanRegisterSystem(
            System::query()
                ->whereKey($args['system_id'])
                ->first()
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'system_id' => ['required', 'int', new SystemBelongsToMemberRule($this->user())],
            ],
        );
    }
}
