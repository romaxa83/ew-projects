<?php

namespace App\GraphQL\Queries\BackOffice\About;

use App\GraphQL\Queries\Common\About\BasePageQuery;
use App\Models\About\Page;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class PageQuery extends BasePageQuery
{
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'published' => [
                    'type' => Type::boolean(),
                    'description' => 'Filter by active pages.'
                ]
            ]
        );
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

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }

    protected function idRuleExists(): Exists
    {
        return Rule::exists(Page::class, 'id');
    }
}
