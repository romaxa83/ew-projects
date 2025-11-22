<?php

namespace Tests\Builders\Dealers;

use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Models\Dealers\Dealer;
use Illuminate\Support\Facades\Hash;
use Tests\Builders\BaseBuilder;

class DealerBuilder extends BaseBuilder
{
    public $addresses;

    public function __construct()
    {
        $this->addresses = collect();
    }

    function modelClass(): string
    {
        return Dealer::class;
    }

    public function setPassword(string $value): self
    {
        $this->data['password'] = Hash::make($value);
        return $this;
    }

    public function setMain(): self
    {
        $this->data['is_main'] = true;
        return $this;
    }

    public function setNotMain(): self
    {
        $this->data['is_main'] = false;
        return $this;
    }

    public function setNotMainCompany(): self
    {
        $this->data['is_main_company'] = false;
        return $this;
    }

    public function setCompany(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function setAddresses(ShippingAddress ...$models): self
    {
        foreach ($models as $model){
            $this->addresses->push($model);
        }
        return $this;
    }

    protected function afterSave($model): void
    {
        if($this->addresses->isNotEmpty()){
            /** @var $model Dealer */
            $model->shippingAddresses()->attach($this->addresses->pluck('id')->toArray());
        }
    }

    protected function afterClear(): void
    {
        $this->addresses = collect();
    }
}
