<?php

namespace WezomCms\Orders\Traits;

use Auth;
use Illuminate\Support\Arr;
use WezomCms\Orders\Models\UserAddress;

trait CourierCheckoutTrait
{
    /**
     * @param $value
     * @param $name
     */
    public function updatedDeliveryData($value, $name)
    {
        if ($name === 'savedAddressId') {
            // If selected address from personal address book - fill fields.
            if ($value && $user = Auth::user()) {
                /** @var UserAddress|null $address */
                if ($address = $user->addresses()->find($value)) {
                    $this->fillDeliveryDataFromUserAddress($address);
                }
            } else {
                // Reset fields
                $this->deliveryData['city'] = null;
                $this->deliveryData['street'] = null;
                $this->deliveryData['house'] = null;
                $this->deliveryData['room'] = null;
                $this->deliveryData['saveAddress'] = true;
            }
        } else {
            if ($name === 'cityQuery') {
                $this->deliveryData['city'] = '';
            }
            if (in_array($name, ['city', 'cityQuery'])) {
                $this->deliveryData['warehouse'] = '';
            }
        }
    }

    public function updatedDeliveryId()
    {
        $this->autocompleteDeliveryAddress();
    }

    protected function autocompleteDeliveryAddress()
    {
        if (array_get($this->deliveryData, 'savedAddressId') === null && $user = Auth::user()) {
            /** @var UserAddress|null $savedAddress */
            $savedAddress = $user->addresses()->where('primary', true)->first();

            if ($savedAddress) {
                $this->fillDeliveryDataFromUserAddress($savedAddress);
            }
        }
    }

    /**
     * @param  UserAddress  $address
     */
    protected function fillDeliveryDataFromUserAddress(UserAddress $address)
    {
        $this->deliveryData['savedAddressId'] = $address->id;
        $this->deliveryData['city'] = $address->city;
        $this->deliveryData['street'] = $address->street;
        $this->deliveryData['house'] = $address->house;
        $this->deliveryData['room'] = $address->room;
        $this->deliveryData['saveAddress'] = false;
    }

    protected function afterCreationOrder()
    {
        if (array_get($this->deliveryData, 'saveAddress') && $user = Auth::user()) {
            /** @var \WezomCms\Users\Models\User $user */
            $user->addresses()->create(Arr::only($this->deliveryData, ['city', 'street', 'house', 'room']));
        }
    }
}
