<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\Sliders;

use App\GraphQL\Types\Sliders\SliderType;
use App\Permissions\Sliders\SliderListPermission;
use App\Repositories\Slider\SliderRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SliderQuery extends BaseQuery
{
    public const NAME = 'slider';
    public const PERMISSION = SliderListPermission::KEY;

    public function __construct(protected SliderRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SliderType::nonNullList();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id()
            ],
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getListWithSort($fields->getRelations(), $args);
    }
}
