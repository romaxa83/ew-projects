<?php

namespace App\Services\Dealers;

use App\Dto\Dealers\DealerDto;
use App\Dto\Dealers\DealerRegisterDto;
use App\Enums\Companies\CompanyStatus;
use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\Events\Dealers\DealerRegisteredEvent;
use App\Models\BaseAuthenticatable;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Notifications\Dealers\SendCredentialsNotification;
use App\Repositories\Companies\ShippingAddressRepository;
use App\Services\Companies\CompanyService;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Notification;

class DealerService
{
    public function __construct(
        protected CompanyService $companyService,
        protected ShippingAddressRepository $addressRepository,
    )
    {}

    public function create(DealerDto $dto): Dealer
    {
        $model = new Dealer();

        $model->company_id = $dto->companyID;
        $model->first_name = $dto->name;
        $model->email = $dto->email;
        $model->email_verified_at = now();
        $model->setLanguage(null);
        $model->setPassword($dto->password);

        $model->save();

        foreach ($dto->shippingAddressIDs as $id){
            if($this->addressRepository->existBy([
                'id' => $id,
                'company_id' => $dto->companyID,
            ])){
                $model->shippingAddresses()->attach($id);
            }
        }

        $dto->companyName = $model->company->business_name;
        event(new CreateOrUpdateDealerEvent($model, $dto));

        return $model;
    }

    public function register(DealerRegisterDto $dto, Company $company): Dealer
    {
        $model = new Dealer();
        $model->company_id = $company->id;
        $model->email = $dto->email;
        $model->is_main_company = true;
        $model->setPassword($dto->password);
        $model->setLanguage(null);
        $model->email_verified_at = now();

        $model->save();

        $this->companyService->setStatus($company, CompanyStatus::REGISTER(), true);

        event(new DealerRegisteredEvent($model));

        return $model;
    }

    public function changePassword(BaseAuthenticatable|Dealer $model, string $password): bool
    {
        return $model->setPassword($password)->save();
    }

    public function sendCredentials(DealerDto $dto): void
    {
        try {
            Notification::route('mail', $dto->email->getValue())
                ->notify(new SendCredentialsNotification($dto));

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    public function toggleMain(Dealer $model): bool
    {
        $model->is_main = !$model->is_main;

        return $model->save();
    }
}
