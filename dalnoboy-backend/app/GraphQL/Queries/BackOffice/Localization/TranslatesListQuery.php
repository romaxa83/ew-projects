<?php


namespace App\GraphQL\Queries\BackOffice\Localization;


use App\GraphQL\Queries\Common\Localization\BaseTranslatesListQuery;

class TranslatesListQuery extends BaseTranslatesListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
