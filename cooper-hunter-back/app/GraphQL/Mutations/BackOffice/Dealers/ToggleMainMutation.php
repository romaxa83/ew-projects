<?php

namespace App\GraphQL\Mutations\BackOffice\Dealers;

use App\GraphQL\Types\NonNullType;
use App\Models\Dealers\Dealer;
use App\Permissions\Dealers\DealerUpdatePermission;
use App\Repositories\Dealers\DealerRepository;
use App\Services\Dealers\DealerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ToggleMainMutation extends BaseMutation
{
    public const NAME = 'dealerToggleMain';
    public const PERMISSION = DealerUpdatePermission::KEY;

    public function __construct(
        protected DealerService $service,
        protected DealerRepository $repo
    )
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ]
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        /** @var $model Dealer */
        $model = $this->repo->getBy('id', $args['id']);
        return $this->service->toggleMain($model);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(Dealer::TABLE, 'id')],
        ];
    }
}

