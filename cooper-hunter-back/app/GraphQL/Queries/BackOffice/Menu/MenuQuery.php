<?php

namespace App\GraphQL\Queries\BackOffice\Menu;

use App\GraphQL\Queries\Common\Menu\BaseMenuQuery;
use App\Models\About\Page;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class MenuQuery extends BaseMenuQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'published' => [
                    'type' => Type::boolean()
                ],
                'page_id' => [
                    'type' => Type::id(),
                    'rules' => [
                        'nullable',
                        'int',
                        Rule::exists(Page::class, 'id')
                    ]
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
}
