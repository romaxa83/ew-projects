<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\FrontOffice\Sliders;

use App\GraphQL\Types\Sliders\SliderType;
use App\Repositories\Slider\SliderRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SliderQuery extends BaseQuery
{
    public const NAME = 'slider';

    public function __construct(protected SliderRepository $repo)
    {}

    public function type(): Type
    {
        return SliderType::nonNullList();
    }

    public function args(): array
    {
        return [];
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

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
