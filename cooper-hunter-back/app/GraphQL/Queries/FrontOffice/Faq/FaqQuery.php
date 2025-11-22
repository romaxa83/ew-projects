<?php

namespace App\GraphQL\Queries\FrontOffice\Faq;

use App\GraphQL\Types\Faq\FaqType;
use App\Models\Faq\Faq;
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
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return [];
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
            ->where('active', true)
            ->with('translation')
            ->latest('sort')
            ->get();
    }
}
