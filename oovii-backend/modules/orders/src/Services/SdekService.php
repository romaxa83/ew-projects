<?php

namespace WezomCms\Orders\Services;

use AntistressStore\CdekSDK2\CdekClientV2;
use AntistressStore\CdekSDK2\Constants;
use AntistressStore\CdekSDK2\Entity\Requests\Contact;
use AntistressStore\CdekSDK2\Entity\Requests\DeliveryPoints;
use AntistressStore\CdekSDK2\Entity\Requests\Item;
use AntistressStore\CdekSDK2\Entity\Requests\Location;
use AntistressStore\CdekSDK2\Entity\Requests\Order as SdekOrder;
use AntistressStore\CdekSDK2\Entity\Requests\Package;
use AntistressStore\CdekSDK2\Entity\Requests\Tariff;
use AntistressStore\CdekSDK2\Entity\Requests\Webhooks;
use AntistressStore\CdekSDK2\Entity\Responses\CitiesResponse;
use AntistressStore\CdekSDK2\Entity\Responses\EntityResponse;
use AntistressStore\CdekSDK2\Entity\Responses\OrderResponse;
use AntistressStore\CdekSDK2\Entity\Responses\RegionsResponse;
use AntistressStore\CdekSDK2\Entity\Responses\TariffListResponse;
use AntistressStore\CdekSDK2\Entity\Responses\TariffResponse;
use Cache;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log;
use ReflectionException;
use ReflectionMethod;
use Throwable;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\SDEK\DeliveryPoint;

class SdekService
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
        try {
            $regions = Cache::remember(
                self::REGIONS_KEY,
                86400,
                function () {
                    $sdekRequest = (new Location())
                        ->setCountryCodes(self::COUNTRIES)
                        ->setLang('kaz');

                    return collect($this->sdekClient->getRegions($sdekRequest))
                        ->filter(
                            function (RegionsResponse $region) {
                                return !Str::contains($region->getRegion(), self::REGION_EXCEPT);
                            }
                        )
                        ->sortBy(fn (RegionsResponse $region) => trim($region->getRegion()))
                        ->toArray();
                }
            );

            return collect($regions);
        } catch (Exception $e) {
            return collect();
        }
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
            $cities = Cache::remember(
                $this->getCitiesCacheKey($regionCode),
                86400,
                function () use ($regionCode) {
                    $sdekRequest = (new Location())
                        ->setCountryCodes(self::COUNTRIES)
                        ->setRegionCode($regionCode)
                        ->setSize(self::PAGE_LIMIT);

                    return collect($this->sdekClient->getCities($sdekRequest))
                        ->sortBy(fn (CitiesResponse $city) => trim($city->getCity()))
                        ->mapWithKeys(fn($city) => [ $city->getCode() => $city ])
                        ->toArray();
                }
            );

            return collect($cities);
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

    public function getRegion(int $regionCode): ?RegionsResponse
    {
        try {
            $regions = $this->getRegions();

            return $regions->first(fn(RegionsResponse $region) => $region->getRegionCode() === $regionCode);
        } catch (Exception $e) {
            return null;
        }
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

    public function getDefaultTariff(string $cityTo, array $from): Collection
    {
        try {
            $tariffCode = $this->getDefaultTariffCode();
            $tariffs = collect();
            $tariffs->put($tariffCode, collect());

            foreach ($from as $providerId => $providerData) {
                $cityFrom = $providerData[0]['city_code'];

                $sdekRequest = (new Tariff())
                    ->setCityCodes($cityFrom, (int)$cityTo)
                    ->setTariffCode($tariffCode)
                    ->setCurrency(self::CURRENCY);

                foreach ($providerData as $productData) {
                    $package = (new Package())
                        ->setWeight($productData['weight'])
                        ->setLength($productData['length'])
                        ->setWidth($productData['width'])
                        ->setHeight($productData['height']);

                    $sdekRequest->setPackages($package);
                }

                $tariff = $this->sdekClient->calculateTariff($sdekRequest);

                $tariffs->get($tariffCode)->put($providerId, $tariff);
            }

            $tariffs = $tariffs
                ->map(function (Collection $items) use ($tariffCode) {
                    return $items
                        ->map(function (TariffResponse $tariff) use ($tariffCode) {
                            return new TariffListResponse([
                                'tariff_name' => __('cms-orders::site.sdek.tariffs.' . $tariffCode),
                                'tariff_code' => $tariffCode,
                                'delivery_sum' => $tariff->getTotalSum(),
                                'period_min' => $tariff->getPeriodMin(),
                                'period_max' => $tariff->getPeriodMax(),
                            ]);
                        });
                });

            $tariffSum = $tariffs
                ->map(function (Collection $items) use ($tariffCode) {
                    $tariffData = [
                        'tariff_name' => __('cms-orders::site.sdek.tariffs.' . $tariffCode),
                        'tariff_code' => $tariffCode,
                        'delivery_sum' => $items->sum(fn (TariffListResponse $item) => $item->getDeliverySum()),
                        'period_min' => $items->max(fn (TariffListResponse $item) => $item->getPeriodMin()),
                        'period_max' => $items->max(fn (TariffListResponse $item) => $item->getPeriodMax()),
                    ];

                    return new TariffListResponse($tariffData);
                });

            return collect([
                'providers' => $tariffs,
                'sum' => $tariffSum,
            ]);
        } catch (Exception $e) {
            return collect([
                'providers' => collect(),
                'sum' => collect(),
            ]);
        }
    }

    public function getTariffs(string $cityTo, string $postalCode, array $from): Collection
    {
        try {
            $tariffs = collect();

            foreach ($from as $providerId => $providerData) {
                $cityFrom = $providerData[0]['city_code'];

                $sdekRequest = (new Tariff())
                    ->setCityCodes($cityFrom, (int)$cityTo)
                    ->setCurrency(self::CURRENCY);

                foreach ($providerData as $productData) {
                    $package = (new Package())
                        ->setWeight($productData['weight'])
                        ->setLength($productData['length'])
                        ->setWidth($productData['width'])
                        ->setHeight($productData['height']);

                    $sdekRequest->setPackages($package);
                }

                $list = $this->sdekClient->calculateTariffList($sdekRequest);

                collect($list)
                    ->filter(function (TariffListResponse $tariff) {
                        return in_array($tariff->getTariffCode(), $this->tariffs, true);
                    })
                    ->each(function (TariffListResponse $tariff) use ($tariffs, $providerId) {
                        $tariffCode = $tariff->getTariffCode();

                        if (!$tariffs->has($tariffCode)) {
                            $tariffs->put($tariffCode, collect());
                        }

                        $tariffs->get($tariffCode)->put($providerId, $tariff);
                    });
            }

            $tariffs = $tariffs
                ->filter(function (Collection $items) use ($from) {
                    return $items->count() === count($from);
                });

            $tariffSum = $tariffs
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

            return collect([
                'providers' => $tariffs,
                'sum' => $tariffSum,
            ]);
        } catch (Exception $e) {
            return collect([
                'providers' => collect(),
                'sum' => collect(),
            ]);
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
            ->setSender($this->getSender($order))
            ->setTariffCode($order->deliveryInformation->tariff_code)
            ->setRecipient($this->getRecipient($order))
            ->setFromLocation($this->getFromLocation($order))
            ->setToLocation($this->getToLocation($order));

        $packages = $this->getPackages($order);

        foreach ($packages as $package) {
            $sdekOrder->setPackages($package);
        }

        try {
            /** @var EntityResponse $response */
            $response = $this->sdekClient->createOrder($sdekOrder);

            $order->deliveryInformation
                ->setUuid($response->getEntityUuid())
                ->save();
        } catch (Throwable $e) {
            Log::error($e->getMessage() . ', Order id: ' . $order->id);

            $order->status_id = $rollbackStatus;
            $order->deleteLatestHistory();
            $order->saveQuietly();
        }
    }

    private function getPackages(Order $order): array
    {
        $packages = [];

        foreach ($order->items as $index => $orderItem) {
            $item = (new Item())
                ->setName($orderItem->product->name)
                ->setWareKey($orderItem->product->id)
                ->setPayment(0) // Check payment method
                ->setCost($orderItem->price)
                ->setWeight($orderItem->product->weight)
                ->setAmount($orderItem->quantity);

            $packages[] = (new Package())
                ->setNumber($order->id . '-' . ($index + 1))
                ->setWeight($orderItem->getWeight())
                ->setLength($orderItem->getLength())
                ->setWidth($orderItem->getWidth())
                ->setHeight($orderItem->getHeight())
                ->setItems([$item]);
        }

        return $packages;
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

    private function getSender(Order $order): Contact
    {
        return (new Contact())
            ->setName($order->provider->name)
            ->setEmail($order->provider->email)
            ->setPhones($order->provider->phone)
            ->setCompany($order->provider->company);
    }

    public function webhookSubscribe(): string
    {
        $webhook = $this->sdekClient->setWebhooks(
            (new Webhooks())
                ->setUrl(route('api.v1.mobile.sdek.webhooks'))
                ->setType('ORDER_STATUS'));

        return $webhook->getEntityUuid();
    }

    /**
     * @param string $uuid
     * @return bool
     * @throws ReflectionException
     */
    public function webhookUnsubscribe(string $uuid): bool
    {
        // Sorry for that
        $method = new ReflectionMethod($this->sdekClient, 'apiRequest');
        $method->setAccessible(true);

        $method->invoke($this->sdekClient, 'DELETE', Constants::WEBHOOKS_URL . '/' . $uuid);

        return true;
    }

    public function getOrderByUuid(string $uuid): OrderResponse
    {
        return $this->sdekClient->getOrderInfoByUuid($uuid);
    }
}
