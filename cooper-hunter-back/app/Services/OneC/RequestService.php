<?php

namespace App\Services\OneC;

use App\Dto\Payments\PaymentCardDto;
use App\Models\Commercial\CommercialProject;
use App\Models\Companies\Company;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Payments\PaymentCard;
use App\Services\OneC\Commands\CommercialProject\CreateCommercialProject;
use App\Services\OneC\Commands\CommercialProject\UpdateCommercialProject;
use App\Services\OneC\Commands\Company\CreateCompany;
use App\Services\OneC\Commands\Company\UpdateCompany;
use App\Services\OneC\Commands\Order\CreateDealerOrder;
use App\Services\OneC\Commands\Order\PackingSlip\UpdatePackingSlip;
use App\Services\OneC\Commands\Order\UpdateDealerOrder;
use App\Services\OneC\Commands\Payment\AddCardToMember;
use App\Services\OneC\Commands\Payment\DeleteCardFromMember;

class RequestService
{
    public function __construct()
    {}

    public function createCommercialProject(CommercialProject $model)
    {
        return resolve(CreateCommercialProject::class)->handler($model);
    }

    public function updateCommercialProject(CommercialProject $model)
    {
        return resolve(UpdateCommercialProject::class)->handler($model);
    }

    public function createCompany(Company $model)
    {
        return resolve(CreateCompany::class)->handler($model);
    }

    public function updateCompany(Company $model)
    {
        return resolve(UpdateCompany::class)->handler($model);
    }

    public function addPaymentCard(PaymentCard $model, PaymentCardDto $dto)
    {
        return resolve(AddCardToMember::class)->handler($model, [
            'dto' => $dto,
        ]);
    }

    public function deletePaymentCard(PaymentCard $model)
    {
        return resolve(DeleteCardFromMember::class)->handler($model);
    }

    public function createDealerOrder(Order $model)
    {
        return resolve(CreateDealerOrder::class)->handler($model);
    }

    public function updateDealerOrder(Order $model)
    {
        return resolve(UpdateDealerOrder::class)->handler($model);
    }

    public function updateDealerOrderPackingSlip(PackingSlip $model)
    {
        return resolve(UpdatePackingSlip::class)->handler($model);
    }
}
