<?php

namespace App\GraphQL\Queries\FrontOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseShippingAddressListQuery;

class ShippingAddressListQuery extends BaseShippingAddressListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setDealerGuard();
    }

    protected function initArgs(array $args): array
    {
        if(!$this->user()->isMain()){
            unset($args['company_id']);
        }

        if($this->user()->isMain() && !isset($args['company_id'])){
            $companyIds = $this->user()
                ?->company
                ?->corporation
                ?->companies
                ->pluck('id')
                ->toArray();

            $args['company_id'] = $companyIds;
        }
        if($this->user()->isMainCompany() && !$this->user()->isMain()) {
            $args['company_id'] = $this->user()->company_id;
        }
        if($this->user()->isSimple()){
            $args['ids'] = $this->user()->shippingAddresses->pluck('id')->toArray();
        }

        return $args;
    }
}

