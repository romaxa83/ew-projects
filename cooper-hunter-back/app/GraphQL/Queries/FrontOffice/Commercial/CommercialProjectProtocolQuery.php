<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Permissions\Commercial\Commissionings\Answer\CreatePermission;
use App\Repositories\Commercial\Commissioning\ProjectProtocolRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Illuminate\Validation\Rule;

class CommercialProjectProtocolQuery extends BaseQuery
{
    public const NAME = 'commercialProjectProtocol';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected ProjectProtocolRepository $repo)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(ProjectProtocol::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return ProjectProtocolType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ProjectProtocol
    {
        $this->isTechnicianCommercial();

        /** @var $model ProjectProtocol */
        $model = $this->repo->getByFields(['id' => $args['id']]);

        return $model;
    }
}

