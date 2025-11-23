<?php

namespace WezomCms\Orders\Drivers\Delivery;

use Illuminate\Contracts\View\View;
use WezomCms\Orders\Contracts\DeliveryDriverInterface;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Rules\NovaPoshtaCityRef;

class NovaPoshtaPostal implements DeliveryDriverInterface
{
    public const KEY = 'nova-poshta-postal';

    /**
     * @var NovaPoshtaServiceInterface
     */
    protected $service;

    /**
     * NovaPoshtaPostal constructor.
     *
     */
    public function __construct()
    {
        $this->service = app(NovaPoshtaServiceInterface::class);
    }

    /**
     * @return array|string[]
     */
    public function getFormInputs(): array
    {
        return ['cityQuery' => '', 'city' => '', 'warehouse' => ''];
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
            ? $this->getCityWarehousesForSelect($deliveryData['city'])
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

    /**
     * @param  string|null  $ref
     * @return string|null
     */
    protected function getCityName(?string $ref): ?string
    {
        if ($ref) {
            $descriptionKey = app()->getLocale() === 'ru' ? 'DescriptionRu' : 'Description';

            return optional($this->service->getCity($ref))->{$descriptionKey};
        }

        return null;
    }

    /**
     * @param  string|null  $query
     * @return array|string[]
     */
    protected function getCitiesForSelect(?string $query): array
    {
        return $query ? $this->presentData($this->service->getCities($query)) : [];
    }

    /**
     * @param  string|null  $ref
     * @return array|string[]
     */
    protected function getCityWarehousesForSelect(?string $ref): array
    {
        return $ref ? $this->presentData($this->service->getCityWarehouses($ref)) : [];
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
        $cityRef = old('deliveryInformation.city_ref', $storage->city_ref);
        $cityName = $this->getCityName($cityRef);

        return view('cms-orders::admin.drivers.delivery.nova-poshta-postal', [
            'cities' => $cityName ? [$cityRef => $cityName] : [],
            'branches' => $this->getCityWarehousesForSelect($cityRef),
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
            'city' => $this->getCityName($storage->city_ref ?? ''),
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

    /**
     * @param  array  $data
     * @return array
     */
    protected function presentData(array $data): array
    {
        return tap([], function (&$result) use ($data) {
            $descriptionKey = app()->getLocale() === 'ru' ? 'DescriptionRu' : 'Description';
            foreach ($data as $datum) {
                $result[$datum->Ref] = $datum->{$descriptionKey};
            }
        });
    }

    public function handleOrderUpdate(Order $order): void
    {
    }
}
