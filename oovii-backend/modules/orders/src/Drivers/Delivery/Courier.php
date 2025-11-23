<?php

namespace WezomCms\Orders\Drivers\Delivery;

use Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Lang;
use WezomCms\Orders\Contracts\DeliveryDriverInterface;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\UserAddress;

class Courier implements DeliveryDriverInterface
{
    public const KEY = 'courier';

    /**
     * @return array|string[]
     */
    public function getFormInputs(): array
    {
        return array_fill_keys(['city', 'street', 'house', 'room'], '');
    }

    /**
     * @param  array  $deliveryData
     * @return mixed
     */
    public function renderFormInputs(array $deliveryData)
    {
        $userAddresses = collect();
        if ($user = Auth::user()) {
            /** @var Collection|UserAddress[] $userAddresses */
            $userAddresses = $user->addresses()
                ->latest()
                ->get();

            /** @var UserAddress $address */
            if (! $deliveryData && $address = $userAddresses->first()) {
                $deliveryData = $address->only('city', 'street', 'house', 'room');
            }
        }

        return view('cms-orders::site.delivery.courier', compact('deliveryData', 'userAddresses'));
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            [
                'city' => 'required|string|max:255',
                'street' => 'required|string|max:50',
                'house' => 'required|string|max:10',
                'room' => 'nullable|int|min:1',
            ],
            [],
            [
                'city' => __('cms-orders::site.checkout.Locality'),
                'street' => __('cms-orders::site.checkout.Street'),
                'house' => __('cms-orders::site.checkout.House'),
                'room' => __('cms-orders::site.checkout.Room'),
            ]
        ];
    }

    /**
     * Fill database storage.
     *
     * @param  OrderDeliveryInformation  $storage
     * @param  array  $data
     */
    public function fillStorage(OrderDeliveryInformation $storage, array $data)
    {
        $storage->fill(array_only($data, ['city', 'street', 'house', 'room']));
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormInputs(OrderDeliveryInformation $storage): View
    {
        return view('cms-orders::admin.drivers.delivery.courier', compact('storage'));
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormData(OrderDeliveryInformation $storage): View
    {
        return view('cms-orders::admin.drivers.delivery.courier-data', compact('storage'));
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return string
     */
    public function presentDeliveryAddress(OrderDeliveryInformation $storage): string
    {
        return implode(
            ', ',
            array_filter([
                $storage->city,
                $storage->street,
                Lang::get('cms-orders::' . app('side') . '.house') . ' ' . $storage->house,
                $storage->room
                    ? Lang::get('cms-orders::' . app('side') . '.room') . ' ' . $storage->room
                    : null,
            ])
        );
    }

    public function handleOrderUpdate(Order $order): void
    {
    }
}
