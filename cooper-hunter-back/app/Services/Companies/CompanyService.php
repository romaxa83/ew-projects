<?php

namespace App\Services\Companies;

use App\Dto\Companies\CompanyDto;
use App\Dto\Companies\CorporationDto;
use App\Dto\Companies\ManagerDto;
use App\Dto\Companies\ShippingAddressDto;
use App\Enums\Companies\CompanyStatus;
use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use App\Notifications\Companies\SendCodeForDealerNotification;
use App\Repositories\Catalog\Product\ProductRepository;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Notification;
use Throwable;

class CompanyService
{
    public function __construct(
        protected ContactService $serviceContact,
        protected CorporationService $serviceCorporation,
        protected ShippingAddressService $serviceShippingAddress,
        protected ManagerService $serviceManager,
        protected CommercialManagerService $serviceCommercialManager
    ) {
    }

    public function approveCompany(Company $model, $code): Company
    {
        $model->code = $code;
        $this->setStatus($model, CompanyStatus::APPROVE());

        $model->save();

        event(new UpdateCompanyByOnecEvent($model));

        return $model;
    }

    public function updateOnec(Company $model, $data): Company
    {
        if ($code = $data['authorization_code'] ?? false) {
            if ($model->status->isDraft()) {
                $this->approveCompany($model, $code);
            }
        }

        if (isset($data['terms'])) {
            $model->terms = $data['terms'];
        }

        if (!empty(data_get($data, 'corporation'))
            && $model->corporation_id == null
        ) {
            $corporation = $this->serviceCorporation->createOrGet(
                CorporationDto::byArgs([
                    'guid' => data_get($data, 'corporation.guid'),
                    'name' => data_get($data, 'corporation.name'),
                ])
            );
            $model->corporation_id = $corporation->id;
        }

        if (!empty(data_get($data, 'manager'))) {
            $this->serviceManager->createOrUpdate(
                $model,
                ManagerDto::byArgs(data_get($data, 'manager'))
            );
        }
        if (!empty(data_get($data, 'commercial_manager'))) {
            $this->serviceCommercialManager->createOrUpdate(
                $model,
                ManagerDto::byArgs(data_get($data, 'commercial_manager'))
            );
        }

        $model->save();

        return $model;
    }

    public function sendCode(Company $model): void
    {
        try {
            Notification::route('mail', $model->email->getValue())->notify(
                    new SendCodeForDealerNotification($model)
                );
        } catch (Throwable $e) {
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    public function addPrice(Company $model, array $data): array
    {
        $tmp = [];
        $model->load('prices');
        /** @var $productRepository ProductRepository */
        $productRepository = app(ProductRepository::class);

        foreach ($data as $item) {
            /** @var $product Product */
            if ($product = $productRepository->getBy(
                'guid',
                data_get($item, 'product_guid')
            )
            ) {
                $this->createOrUpdatePrice(
                    $model,
                    $product,
                    data_get($item, 'price'),
                    data_get($item, 'description'),
                );
            } else {
                $tmp[] = data_get($item, 'product_guid');
            }
        }

        return $tmp;
    }

    public function createOrUpdatePrice(
        Company $company,
        Product $product,
        $price,
        $desc = null,
    ): Price
    {
        if ($model =
            $company->prices->where('product_id', $product->id)->first()
        ) {
            $model->update(['price' => $price, 'desc' => $desc]);
        } else {
            $model = new Price();
            $model->company_id = $company->id;
            $model->product_id = $product->id;
            $model->price = $price;
            $model->desc = $desc;

            $model->save();
        }

        return $model;
    }

    public function update(Company $model, CompanyDto $dto): Company
    {
        $this->fill($model, $dto);
        if ($dto->corporationID) {
            $model->corporation_id = $dto->corporationID;
        }

        $model->save();

        foreach ($dto->shippingAddresses as $shippingAddressDto) {
            /** @var $shippingAddressDto ShippingAddressDto */
            if ($address =
                $model->shippingAddresses->where('id', $shippingAddressDto->id)
                    ->first()
            ) {
                $this->serviceShippingAddress->update(
                    $shippingAddressDto,
                    $address
                );
            } else {
                $this->serviceShippingAddress->create(
                    $shippingAddressDto,
                    $model
                );
            }
        }

        $this->serviceContact->updateContacts($model, $dto);

        $model->refresh();

        return $model;
    }

    protected function fill(Company $model, CompanyDto $dto): void
    {
        $model->type = $dto->type;
        $model->business_name = $dto->businessName;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->fax = $dto->fax;
        $model->country_id = $dto->address->countryID;
        $model->state_id = $dto->address->stateID;
        $model->city = $dto->address->city;
        $model->address_line_1 = $dto->address->addressLine1;
        $model->address_line_2 = $dto->address->addressLine2;
        $model->zip = $dto->address->zip;
        $model->po_box = $dto->address->poBox;
        $model->taxpayer_id = $dto->taxpayerID;
        $model->tax = $dto->tax;
        $model->websites = $dto->websites;
        $model->marketplaces = $dto->marketplaces;
        $model->trade_names = $dto->tradeNames;
    }

    public function create(CompanyDto $dto): Company
    {
        $model = new Company();

        $this->setStatus($model, CompanyStatus::DRAFT());
        $this->fill($model, $dto);

        $model->save();

        foreach ($dto->shippingAddresses as $shippingAddressDto) {
            /** @var $shippingAddressDto ShippingAddressDto */
            $this->serviceShippingAddress->create($shippingAddressDto, $model);
        }

        $this->serviceContact->createContacts($model, $dto);

        foreach ($dto->media as $image) {
            $model->addMedia($image)->toMediaCollection(
                    $model->getMediaCollectionName()
                );
        }


        return $model;
    }

    public function setStatus(
        Company $model,
        CompanyStatus $status,
        bool $save = false
    ): void
    {
        $model->status = $status;
        if ($save) {
            $model->save();
        }
    }
}
