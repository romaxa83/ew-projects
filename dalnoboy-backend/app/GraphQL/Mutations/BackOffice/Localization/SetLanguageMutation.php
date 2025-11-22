<?php


namespace App\GraphQL\Mutations\BackOffice\Localization;


use App\GraphQL\Mutations\Common\Localization\BaseSetLanguageMutation;

class SetLanguageMutation extends BaseSetLanguageMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
