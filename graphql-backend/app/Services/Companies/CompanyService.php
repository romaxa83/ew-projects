<?php

namespace App\Services\Companies;

use App\Dto\Companies\CompanyDto;
use App\Events\Companies\CompanyCreatedEvent;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;

class CompanyService
{
    public function create(?string $lang = null): Company
    {
        $company = Company::query()->create();

        $company
            ->setLanguage($lang)
            ->save();

        event(new CompanyCreatedEvent($company));

        return $company;
    }

    public function setOwner(User $user, Company $company): void
    {
        CompanyUser::query()->create(
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'state' => Company::STATE_OWNER,
            ]
        );
    }

    public function update(Company $company, CompanyDto $dto): Company
    {
        $company->name = $dto->getName();
        $company->setLanguage($dto->getLang());

        if ($company->isDirty()) {
            $company->save();
        }

        return $company;
    }
}
