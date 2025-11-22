<?php


namespace App\GraphQL\Queries\FrontOffice\Localization;


use App\GraphQL\Queries\Common\Localization\BaseTranslatesListQuery;

class TranslatesListQuery extends BaseTranslatesListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
