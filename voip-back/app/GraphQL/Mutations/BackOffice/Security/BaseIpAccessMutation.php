<?php

namespace App\GraphQL\Mutations\BackOffice\Security;

use App\GraphQL\Types\Security\IpAccessType;
use App\Models\Security\IpAccess;
use App\Services\Security\IpAccessService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

abstract class BaseIpAccessMutation extends BaseMutation
{
    public function __construct(protected IpAccessService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return IpAccessType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'address' => [
                'type' => Type::string(),

            ],
            'description' => [
                'type' => Type::string(),

            ],
            'active' => [
                'type' => Type::boolean(),
            ],
        ];
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : [
                'id' => ['required', 'integer', Rule::exists(IpAccess::TABLE, 'id'),],
                'address' => ['required', 'string', 'ipv4',],
                'description' => ['nullable', 'string',],
                'active' => ['required', 'boolean'],
            ];
    }
}
