<?php

namespace App\GraphQL\Queries\FrontOffice\About;

use App\GraphQL\Queries\Common\About\BasePageQuery;
use App\Models\About\Page;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class PageQuery extends BasePageQuery
{
    protected function setQueryGuard(): void
    {
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }

    protected function idRuleExists(): Exists
    {
        return Rule::exists(Page::class, 'id')
            ->where('active', true);
    }
}
