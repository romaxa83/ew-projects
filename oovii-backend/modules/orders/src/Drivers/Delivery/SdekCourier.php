<?php

namespace WezomCms\Orders\Drivers\Delivery;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Rules\SdekRegion;
use WezomCms\Orders\Services\SdekService;

class SdekCourier extends Courier
{
    public const KEY = 'sdek-courier';

    protected SdekService $service;

    public function __construct()
    {
        $this->service = app(SdekService::class);
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormInputs(OrderDeliveryInformation $storage): View
    {
        $regionCode = old('deliveryInformation.region_code', $storage->region_code);
        $cityCode = old('deliveryInformation.city_code', $storage->city_code);
        $cityName = $this->getCityName($regionCode, $cityCode);

        $tariffs = [];
        foreach ($this->service->getTariffCodes() as $tariffCode) {
            $tariffs[$tariffCode] = __('cms-orders::admin.sdek.tariffs.' . $tariffCode);
        }

        return view('cms-orders::admin.drivers.delivery.sdek-courier', [
            'regions' => $this->getRegionsForSelect(),
            'cities' => $cityName ? [ $cityCode => $cityName ] : [],
            'storage' => $storage,
            'tariffs' => $tariffs,
            'deliveryStatuses' => $storage->delivery_statuses,
        ]);
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormData(OrderDeliveryInformation $storage): View
    {
        $region = $this->service->getRegion($storage->region_code);
        $city = $this->getCityName($storage->region_code, $storage->city_code);

        return view('cms-orders::admin.drivers.delivery.sdek-courier-data', [
            'storage' => $storage,
            'region' => optional($region)->getRegion(),
            'city' => $city,
            'deliveryStatuses' => $storage->delivery_statuses,
        ]);
    }

    protected function getRegionsForSelect(): Collection
    {
        return $this->service->getRegionsForSelect();
    }

    protected function getCityName(?string $regionRef, ?string $cityRef): ?string
    {
        if ($regionRef && $cityRef) {
            return optional($this->service->getCity($regionRef, $cityRef))->getCity();
        }

        return null;
    }

    public function getValidationRules(): array
    {
        return [
            [
                'region_code' => ['required', new SdekRegion()],
                'city_code' => 'required|integer',
                'postal_code' => 'required|string|max:6',
                'tariff_code' => ['required', 'integer', Rule::in($this->service->getTariffCodes())],
                'address' => 'required|string|max:255',
                'city' => 'nullable|string|max:255',
                'time' => 'nullable|string|max:255',
            ],
            [],
            [
                'region_code' => __('cms-orders::site.checkout.Region'),
                'city_code' => __('cms-orders::site.checkout.Locality'),
                'postal_code' => __('cms-orders::site.checkout.Postal code'),
                'tariff_code' => __('cms-orders::site.checkout.Tariff'),
                'address' => __('cms-orders::site.checkout.Address'),
                'city' => __('cms-orders::site.checkout.City'),
                'time' => __('cms-orders::site.checkout.Time'),
            ]
        ];
    }

    public function handleOrderUpdate(Order $order): void
    {
        $rollbackStatus = $order->getOriginal('status_id');
        $order->refresh();

        if ($order->status->isReady()) {
            $this->service->createOrder($order, $rollbackStatus);
        }
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
                optional($this->service->getRegion($storage->region_code))->getRegion(),
                $this->getCityName($storage->region_code, $storage->city_code),
                $storage->address,
            ])
        );
    }
}
