<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Types\Dictionaries\TireChangesReasonType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\TireChangesReasonService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class TireChangesReasonsQuery extends BaseQuery
{
    public const NAME = 'tireChangesReasons';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private TireChangesReasonService $service)
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return TireChangesReasonType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->show(
            $fields->getRelations(),
            $fields->getSelect()
        );
    }
}
