<?php

namespace WezomCms\Orders\Drivers\Delivery;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use WezomCms\Orders\Contracts\DeliveryDriverInterface;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Rules\NovaPoshtaCityRef;
use WezomCms\Orders\Services\SdekService;

class SdekPostal implements DeliveryDriverInterface
{
    public const KEY = 'sdek-postal';

    protected SdekService $service;

    public function __construct()
    {
        $this->service = app(SdekService::class);
    }

    public function getFormInputs(): array
    {
        return [
            'region' => '',
            'cityQuery' => '',
            'city' => '',
            'deliveryPoint' => '',
        ];
    }

    /**
     * @param  array  $deliveryData
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function renderFormInputs(array $deliveryData)
    {
        $cities = $deliveryData['cityQuery'] ?? false
            ? $this->getCitiesForSelect($deliveryData['cityQuery'])
            : [];

        $warehouses = $deliveryData['city'] ?? false
            ? $this->getDeliveryPointsForSelect($deliveryData['city'])
            : [];

        return view('cms-orders::site.delivery.nova-poshta-postal', compact('cities', 'deliveryData', 'warehouses'));
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            [
                'city' => ['bail', 'required', 'string', new NovaPoshtaCityRef()],
                'warehouse' => 'required|string',
            ],
            [],
            [
                'city' => __('cms-orders::site.checkout.City'),
                'warehouse' => __('cms-orders::site.checkout.Branch'),
            ]
        ];
    }

    protected function getCityName(?string $regionRef, ?string $cityRef): ?string
    {
        if ($regionRef && $cityRef) {
            return optional($this->service->getCity($regionRef, $cityRef))->getCity();
        }

        return null;
    }

    protected function getRegionsForSelect(): Collection
    {
        return $this->service->getRegionsForSelect();
    }

    /**
     * @param  string|null  $query
     * @return array|string[]
     */
    protected function getCitiesForSelect(?string $query): array
    {
        return $query ? $this->presentData($this->service->getCities($query)) : [];
    }

    protected function getDeliveryPointsForSelect(?string $cityRef): Collection
    {
        return $cityRef ? $this->service->getDeliveryPointsForSelect($cityRef) : collect();
    }

    /**
     * @inheritDoc
     */
    public function fillStorage(OrderDeliveryInformation $storage, array $data)
    {
        $storage->fill([
            'city_ref' => $data['city'],
            'branch_ref' => $data['warehouse'],
        ]);
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormInputs(OrderDeliveryInformation $storage): View
    {
        $regionRef = old('deliveryInformation.region_ref', $storage->region_ref);
        $cityRef = old('deliveryInformation.city_ref', $storage->city_ref);
        $cityName = $this->getCityName($regionRef, $cityRef);

        return view('cms-orders::admin.drivers.delivery.sdek-postal', [
            'regions' => $this->getRegionsForSelect(),
            'cities' => $cityName ? [ $cityRef => $cityName ] : [],
            'branches' => $this->getDeliveryPointsForSelect($cityRef),
            'storage' => $storage,
        ]);
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormData(OrderDeliveryInformation $storage): View
    {
        return view('cms-orders::admin.drivers.delivery.nova-poshta-postal-data', [
            'city' => $this->getCityName($storage->region_code, $storage->city_code ?? ''),
            'branch' => array_get($this->getCityWarehousesForSelect($storage->city_ref), $storage->branch_ref),
            'ttn' => $storage->ttn,
        ]);
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
                $this->getCityName($storage->city_ref),
                array_get($this->getCityWarehousesForSelect($storage->city_ref), $storage->branch_ref)
            ])
        );
    }

    public function handleOrderUpdate(Order $order): void
    {
    }
}
