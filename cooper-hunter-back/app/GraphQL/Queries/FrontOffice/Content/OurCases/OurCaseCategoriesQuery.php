<?php

namespace App\GraphQL\Queries\FrontOffice\Content\OurCases;

use App\GraphQL\Queries\Common\Content\OurCase\BaseOurCaseCategoriesQuery;
use App\Models\Content\OurCases\OurCaseCategory;
use Illuminate\Database\Eloquent\Builder;
use Rebing\GraphQL\Support\SelectFields;

class OurCaseCategoriesQuery extends BaseOurCaseCategoriesQuery
{
    protected function getQuery(array $args, SelectFields $fields): Builder|OurCaseCategory
    {
        $args['active'] = true;

        return parent::getQuery($args, $fields);
    }
}
