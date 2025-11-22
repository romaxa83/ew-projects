<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\DictionaryType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\DictionaryService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseDictionariesQuery extends BaseQuery
{
    public const NAME = 'dictionaries';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private DictionaryService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return DictionaryType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array {
        return $this->service->getList();
    }
}
