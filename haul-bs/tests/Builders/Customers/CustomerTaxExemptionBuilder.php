<?php

namespace Tests\Builders\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use App\Services\Customers\CustomerTaxExemptionService;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class CustomerTaxExemptionBuilder extends BaseBuilder
{
    protected ?Customer $customer;
    protected ?UploadedFile $file;

    function modelClass(): string
    {
        return CustomerTaxExemption::class;
    }

    public function date_active_to(string $value): self
    {
        $this->data['date_active_to'] = $value;
        return $this;
    }

    public function customer(Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }


    public function file(UploadedFile $file): self
    {
        $this->file = $file;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model CustomerTaxExemption */
        if(!empty($this->customer)){
            $model->customer_id = $this->customer->id;
        }

        if(!empty($this->file)){
            /** @var $service CustomerTaxExemptionService */
            $service = resolve(CustomerTaxExemptionService::class);
            $service->uploadFile($model, $this->file);
        }
    }

    protected function afterClear(): void
    {
        $this->customer = null;
        $this->file = null;
    }
}
