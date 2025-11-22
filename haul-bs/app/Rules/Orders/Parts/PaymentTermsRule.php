<?php

namespace App\Rules\Orders\Parts;

use App\Enums\Orders\Parts\ShippingMethod;
use App\Http\Requests\Orders\Parts\OrderPartsItemRequest;
use App\Http\Requests\Orders\Parts\OrderPartsRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;

class PaymentTermsRule implements Rule
{
    public function __construct(
        protected OrderPartsRequest $request
    )
    {}

    public function passes($attribute, $value): bool
    {
        if(isset($this->request['shipping_methods']) && !empty($this->request['shipping_methods'])){
            $pickup = false;
            foreach($this->request['shipping_methods'] as $method){
                if(!$pickup){
                    $pickup = $method['name'] == ShippingMethod::Pickup();
                }
            }
dd($pickup);
            return $pickup;
        }

        return false;
    }

    public function message(): string
    {
        return __("validation.custom.order.parts.few_quantities");
    }
}


