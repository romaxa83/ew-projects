<?php

namespace WezomCms\Orders\Services;

use AntistressStore\CdekSDK2\CdekClientV2;
use AntistressStore\CdekSDK2\Entity\Requests\Contact;
use AntistressStore\CdekSDK2\Entity\Requests\DeliveryPoints;
use AntistressStore\CdekSDK2\Entity\Requests\Item;
use AntistressStore\CdekSDK2\Entity\Requests\Location;
use AntistressStore\CdekSDK2\Entity\Requests\Order as SdekOrder;
use AntistressStore\CdekSDK2\Entity\Requests\Package;
use AntistressStore\CdekSDK2\Entity\Requests\Tariff;
use AntistressStore\CdekSDK2\Entity\Responses\CitiesResponse;
use AntistressStore\CdekSDK2\Entity\Responses\EntityResponse;
use AntistressStore\CdekSDK2\Entity\Responses\RegionsResponse;
use AntistressStore\CdekSDK2\Entity\Responses\TariffListResponse;
use Cache;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log;
use Throwable;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\SDEK\DeliveryPoint;

class TestSdekService extends SdekService
{
    public const COUNTRIES = 'KZ';
    public const PAGE_LIMIT = 500;
    public const CITIES_LIMIT = 10;
    public const REGION_EXCEPT = ['устарела'];
    public const CURRENCY = 2;

    public const REGIONS_KEY = 'sdek_regions';
    public const CITIES_KEY = 'sdek_cities_';

    private CdekClientV2 $sdekClient;

    private array $tariffs;

    private array $regionsData = [
        [
            'country' => 'Казахстан',
            'country_code' => 'KZ',
            'region_code' => 299,
            'region' => 'Алматинская область',
        ],
        [
            'country' => 'Казахстан',
            'country_code' => 'KZ',
            'region_code' => 294,
            'region' => 'Актюбинская область',
        ],
        [
            'country' => 'Казахстан',
            'country_code' => 'KZ',
            'region_code' => 900,
            'region' => 'Туркестанская область',
        ],
        [
            'country' => 'Казахстан',
            'country_code' => 'KZ',
            'region_code' => 402,
            'region' => 'Восточно-Казахстанская область',
        ],
        [
            'country' => 'Казахстан',
            'country_code' => 'KZ',
            'region_code' => 500,
            'region' => 'Карагандинская область',
        ],
    ];

    public function __construct()
    {
        $this->sdekClient = resolve('sdekClient');
        $this->tariffs = config('cms.orders.delivery-and-payment.sdek.tariffs', []);
    }

    public function getTariffCodes(): array
    {
        return $this->tariffs;
    }

    public function getDefaultTariffCode(): int
    {
        return current($this->tariffs);
    }

    public function getRegions(): Collection
    {
        return collect($this->regionsData)
            ->mapInto(RegionsResponse::class);
    }

    public function getRegionsForSelect(): Collection
    {
        return $this->getRegions()
            ->mapWithKeys(function (RegionsResponse $region) {
                return [ $region->getRegionCode() => $region->getRegion() ];
            })
            ->sort();
    }

    public function getCities(int $regionCode): Collection
    {
        try {
            return Cache::remember(
                $this->getCitiesCacheKey($regionCode),
                86400,
                function () use ($regionCode) {
                    $sdekRequest = (new Location())
                        ->setCountryCodes(self::COUNTRIES)
                        ->setRegionCode($regionCode)
                        ->setSize(self::PAGE_LIMIT);

                    return collect($this->sdekClient->getCities($sdekRequest))
                        ->mapWithKeys(fn($city) => [ $city->getCode() => $city ]);
                }
            );
        } catch (Exception $e) {
            return collect();
        }
    }

    public function getCitiesForSelect(int $regionCode, ?string $query = null, ?int $limit = null): Collection
    {
        $cities = $this->getCities($regionCode);

        if ($query) {
            $query = Str::lower($query);

            $cities = $cities->filter(function (CitiesResponse $city) use ($query) {
                return Str::contains(Str::lower($city->getCity()), $query);
            });
        }

        $limit = $limit ?? self::CITIES_LIMIT;

        return $cities->mapWithKeys(fn($city) => [
            $city->getCode() => $city->getCity(),
        ])
            ->take($limit);
    }

    public function getCity(int $regionCode, int $cityCode): ?CitiesResponse
    {
        try {
            $cities = $this->getCities($regionCode);

            return $cities->get($cityCode);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getDeliveryPoints(int $cityCode): Collection
    {
        try {
            $sdekRequest = (new DeliveryPoints())
                ->setCityCode($cityCode);

            return collect($this->sdekClient->getDeliveryPoints($sdekRequest));
        } catch (Exception $e) {
            return collect();
        }
    }

    public function getDeliveryPointsForSelect(int $cityCode): Collection
    {
        try {
            $deliveryPoints = $this->getDeliveryPoints($cityCode);

            return $deliveryPoints
                ->mapInto(DeliveryPoint::class)
                ->mapWithKeys(fn($point) => [ $point->getCode() => $point->getFullName() ])
                ->sort();
        } catch (Exception $e) {
            return collect();
        }
    }

    public function getTariffs(string $cityTo, string $postalCode, array $from): Collection
    {
        try {
            $tariffs = collect();

            foreach ($from as $cityFrom => $weight) {
                $sdekRequest = (new Tariff())
                    ->setCityCodes($cityFrom, (int)$cityTo)
                    ->setPackageWeight($weight)
                    ->setCurrency(self::CURRENCY);

                $list = $this->sdekClient->calculateTariffList($sdekRequest);
                collect($list)
                    ->filter(function (TariffListResponse $tariff) {
                        return in_array($tariff->getTariffCode(), $this->tariffs, true);
                    })
                    ->each(function (TariffListResponse $tariff) use ($tariffs) {
                        $tariffCode = $tariff->getTariffCode();

                        if (!$tariffs->has($tariffCode)) {
                            $tariffs->put($tariffCode, collect());
                        }

                        $tariffs->get($tariffCode)->push($tariff);
                    });
            }

            return $tariffs
                ->filter(function (Collection $items) use ($from) {
                    return $items->count() === count($from);
                })
                ->map(function (Collection $items, $index) {
                    $tariffData = [
                        'tariff_name' => __('cms-orders::site.sdek.tariffs.' . $index),
                        'tariff_code' => $index,
                        'delivery_sum' => $items->sum(fn (TariffListResponse $item) => $item->getDeliverySum()),
                        'period_min' => $items->max(fn (TariffListResponse $item) => $item->getPeriodMin()),
                        'period_max' => $items->max(fn (TariffListResponse $item) => $item->getPeriodMax()),
                    ];

                    return new TariffListResponse($tariffData);
                });
        } catch (Exception $e) {
            return collect();
        }
    }

    private function getCitiesCacheKey(int $region): string
    {
        return self::CITIES_KEY . $region;
    }

    public function createOrder(Order $order, int $rollbackStatus): void
    {
        $sdekOrder = (new SdekOrder())
            ->setType(1)
            ->setNumber($order->id)
            ->setTariffCode($order->deliveryInformation->tariff_code)
            ->setRecipient($this->getRecipient($order))
            ->setFromLocation($this->getFromLocation($order))
            ->setToLocation($this->getToLocation($order))
            ->setPackages($this->getPackages($order));

        try {
            /** @var EntityResponse $response */
            $response = $this->sdekClient->createOrder($sdekOrder);

            $sdekOrder = $this->sdekClient->getOrderInfoByImNumber($order->id);

            $order->deliveryInformation
                ->setUuid($response->getEntityUuid())
                ->setTtn($sdekOrder->getCdekNumber())
                ->save();
        } catch (Throwable $e) {
            Log::error($e->getMessage() . ', Order id: ' . $order->id);

            $order->status_id = $rollbackStatus;
            $order->save();
        }
    }

    private function getPackages(Order $order): Package
    {
        $weight = $order->items
            ->reduce(function (int $sum, OrderItem $item) {
                return $sum + $item->quantity * $item->product->weight;
            }, 0);

        $items = [];

        foreach ($order->items as $item) {
            $items[] = (new Item())
                ->setName($item->product->name)
                ->setWareKey($item->product->id)
                ->setPayment($item->purchase_price) // Check payment method
                ->setCost($item->price)
                ->setWeight($item->product->weight)
                ->setAmount($item->quantity);
        }

        return (new Package())
            ->setNumber($order->id . '-1')
            ->setWeight($weight)
            ->setItems($items);
    }

    private function getFromLocation(Order $order): Location
    {
        $provider = $order->provider;

        return (new Location())
            ->setRegionCode($provider->region_code)
            ->setCode($provider->city_code)
            ->setAddress($provider->address);
    }

    private function getToLocation(Order $order): Location
    {
        $deliveryInfo = $order->deliveryInformation;

        return (new Location())
            ->setRegionCode((int) $deliveryInfo->region_code)
            ->setCode((int) $deliveryInfo->city_code)
            ->setPostalCode($deliveryInfo->postal_code)
            ->setAddress($deliveryInfo->address);
    }

    private function getRecipient(Order $order): Contact
    {
        $client = $order->getCustomer();

        $contact = (new Contact())
            ->setName($client->getFullName())
            ->setPhones($client->getPhone());

        if ($client->getEmail()) {
            $contact->setEmail($client->getEmail());
        }

        return $contact;
    }
}
