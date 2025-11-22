<?php

namespace App\Services\Customers;

use App\Dto\Customers\CustomerDto;
use App\Dto\Customers\CustomerTaxExemptionDto;
use App\Dto\Customers\CustomerTaxExemptionEComDto;
use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerTaxExemptionType;
use App\Events\Events\Customers\CreateCustomerTaxExemptionEComEvent;
use App\Events\Events\Customers\CreateCustomerTaxExemptionEvent;
use App\Events\Events\Customers\AcceptedCustomerTaxExemptionEvent;
use App\Events\Events\Customers\DeclineCustomerTaxExemptionEvent;
use App\Events\Events\Customers\DeleteCustomerTaxExemptionEvent;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Http\UploadedFile;

class CustomerTaxExemptionService
{
    public function __construct()
    {}

    public function create(Customer $customer, CustomerTaxExemptionDto $dto): Customer
    {
        return make_transaction(function () use ($customer, $dto){
            /** @var CustomerTaxExemption $model */
            $model = $customer->taxExemption()->create([
                'date_active_to' => $dto->date_active_to,
                'status' => CustomerTaxExemptionStatus::ACCEPTED,
                'type' => CustomerTaxExemptionType::BODY,
            ]);

            if($file = $dto->file){
                $this->uploadFile($model, $file);
            }

            event(new CreateCustomerTaxExemptionEvent($model));
            return $customer;
        });
    }

    public function createECom(Customer $customer, CustomerTaxExemptionEComDto $dto): Customer
    {
        return make_transaction(function () use ($customer, $dto){
            /** @var CustomerTaxExemption $model */
            $customer->taxExemption()->create([
                'link' => $dto->link,
                'file_name' => $dto->file_name,
                'status' => CustomerTaxExemptionStatus::UNDER_REVIEW,
                'type' => CustomerTaxExemptionType::ECOM,
            ]);

            event(new CreateCustomerTaxExemptionEComEvent($customer));
            return $customer;
        });
    }

    public function accepted(Customer $customer, string $date): Customer
    {
        return make_transaction(function () use ($customer, $date){
            /** @var CustomerTaxExemption $model */
            $taxExemption = $customer->taxExemption;
            $taxExemption?->update([
                'date_active_to' => $date,
                'status' => CustomerTaxExemptionStatus::ACCEPTED,
            ]);

            if ($taxExemption) {
                event(new AcceptedCustomerTaxExemptionEvent($taxExemption));
            }
            return $customer;
        });
    }

    public function decline(Customer $customer): Customer
    {
        return make_transaction(function () use ($customer){
            /** @var CustomerTaxExemption $model */
            $taxExemption = $customer->taxExemption;
            $taxExemption?->update([
                'status' => CustomerTaxExemptionStatus::DECLINED,
            ]);

            if ($taxExemption) {
                event(new DeclineCustomerTaxExemptionEvent($taxExemption));
            }
            return $customer;
        });
    }

    public function delete(Customer $customer): Customer
    {
        return make_transaction(function () use ($customer){
            /** @var CustomerTaxExemption $model */
            $customer->taxExemption()->delete();

            return $customer;
        });
    }

    public function uploadFile(CustomerTaxExemption $model, UploadedFile $file): void
    {
        $model->addMedia($file)->toMediaCollection();
    }
}
