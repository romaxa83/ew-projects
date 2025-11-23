<?php

namespace WezomCms\Orders\Services;

use Cache;
use NovaPoshta\ApiModels\Address;
use NovaPoshta\Config;
use NovaPoshta\MethodParameters\Address_getCities;
use NovaPoshta\MethodParameters\Address_getSettlements;
use NovaPoshta\MethodParameters\Address_getWarehouses;
use stdClass;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;

/**
 * Class NovaPoshtaService
 *
 * @package WezomCms\Orders\Services
 */
class NovaPoshtaService implements NovaPoshtaServiceInterface
{
    /**
     * NovaPoshtaService constructor.
     *
     * @param  string  $key
     * @param  string  $locale
     */
    public function __construct(string $key, string $locale)
    {
        Config::setApiKey($key);
        Config::setFormat(Config::FORMAT_JSONRPC2);
        if (defined(Config::class . '::LANGUAGE_' . strtoupper($locale))) {
            Config::setLanguage($locale);
        }
    }

    /**
     * Get all areas
     *
     * @return array
     */
    public function getAreas(): array
    {
        return Cache::remember('nova-poshta-areas', now()->addDay(), function () {
            return Address::getAreas()->data;
        });
    }

    /**
     * Get settlements using findByString attribute
     *
     * @param  string  $query
     * @param  bool|null  $hasWarehouses
     * @return array
     */
    public function getSettlements(string $query, bool $hasWarehouses = null): array
    {
        $key = 'nova-poshta-settlements';
        $data = new Address_getSettlements();
        if ($query) {
            $data->setFindByString($query);
            $key .= '-' . urlencode(strtolower($query));
        }
        if (!is_null($hasWarehouses)) {
            $data->Warehouse = $hasWarehouses;
            $key .= '-' . $hasWarehouses;
        }

        return Cache::remember($key, now()->addDay(), function () use ($data) {
            return Address::getSettlements($data)->data;
        });
    }

    /**
     * Get city using ref attribute
     *
     * @param  string  $ref
     * @return stdClass|null
     */
    public function getCity(string $ref): ?stdClass
    {
        $data = new Address_getCities();
        $data->setRef($ref);
        $data->Limit = 1;

        $key = 'nova-poshta-city-' . urlencode(strtolower($ref));

        return Cache::remember($key, now()->addMonth(), function () use ($data) {
            $response = Address::getCities($data);

            return $response->data ? current($response->data) : null;
        });
    }

    /**
     * Get cities using findByString attribute
     *
     * @param  string  $query
     * @return array
     */
    public function getCities(string $query): array
    {
        $data = new Address_getCities();
        $data->setFindByString($query);
        $data->setPage(1);
        $data->Limit = 10;

        $key = 'nova-poshta-cities-' . urlencode(strtolower($query));

        return Cache::remember($key, now()->addDay(), function () use ($data) {
            return Address::getCities($data)->data;
        });
    }

    /**
     * Get city warehouses using city ref
     *
     * @param  string  $cityRef
     * @return array
     */
    public function getCityWarehouses(string $cityRef): array
    {
        $data = new Address_getWarehouses();
        $data->setCityRef($cityRef);

        return Cache::remember("nova-poshta-city-warehouses-{$cityRef}", now()->addDay(), function () use ($data) {
            return Address::getWarehouses($data)->data;
        });
    }
}
