<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Permissions\Commercial\Commissionings\Protocol\DeletePermission;
use App\Repositories\Commercial\Commissioning\ProtocolRepository;
use App\Services\Commercial\Commissioning\ProtocolService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteMutation extends BaseMutation
{
    public const NAME = 'commissioningProtocolDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected ProtocolService $service,
        protected ProtocolRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
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
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                $this->repo->getByFields(['id' => $args['id']])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(Protocol::TABLE, 'id')],
        ];
    }
}


