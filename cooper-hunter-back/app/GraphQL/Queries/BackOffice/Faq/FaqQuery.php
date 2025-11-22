<?php

namespace App\GraphQL\Queries\BackOffice\Faq;

use App\GraphQL\Types\Faq\FaqType;
use App\Models\Faq\Faq;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class FaqQuery extends BaseQuery
{
    public const NAME = 'faq';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
        ];
    }

    public function type(): Type
    {
        return FaqType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields,
    ): Collection {
        return Faq::query()
            ->filter($args)
            ->with(['translation', 'translations'])
            ->latest('sort')
            ->get();
    }
}
