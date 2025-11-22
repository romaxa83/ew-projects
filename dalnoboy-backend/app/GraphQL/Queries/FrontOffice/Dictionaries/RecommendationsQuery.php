<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseRecommendationsQuery;

class RecommendationsQuery extends BaseRecommendationsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
