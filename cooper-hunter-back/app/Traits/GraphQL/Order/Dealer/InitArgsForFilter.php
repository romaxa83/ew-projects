<?php

namespace App\Traits\GraphQL\Order\Dealer;

trait InitArgsForFilter
{
    protected function init(array $args): array
    {
        if($this->user()->isMain()){
            $tmp = $this->user()?->company?->corporation?->companies;
            $ids = [];
            $tmp->map(function ($comp) use (&$ids){
                $ids = array_merge($ids, $comp->dealers->pluck('id')->toArray());
            });

            $args['dealer_id'] = $ids;
        }
        if($this->user()->isMainCompany() && !$this->user()->isMain()){
            $ids = $this->user()?->company->dealers->pluck('id')->toArray();
            $args['dealer_id'] = $ids;
        }
        if($this->user()->isSimple()){
            $shippingAddressIds = $this->user()?->shippingAddresses->pluck('id')->toArray();
            $args['dealer_id'] = $this->user()->id;
            $args['shipping_address_id'] = $shippingAddressIds;
        }

        return $args;
    }
}
