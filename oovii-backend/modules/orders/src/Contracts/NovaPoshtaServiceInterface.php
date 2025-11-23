<?php

namespace WezomCms\Orders\Contracts;

use stdClass;

/**
 * Interface NovaPoshtaServiceInterface
 *
 * @package WezomCms\Orders\Contracts
 */
interface NovaPoshtaServiceInterface
{
    /**
     * NovaPoshtaServiceInterface constructor.
     *
     * @param  string  $key
     * @param  string  $locale
     */
    public function __construct(string $key, string $locale);

    /**
     * Get all areas
     *
     * @return array
     */
    public function getAreas(): array;

    /**
     * Get settlements using findByString attribute
     *
     * @param  string  $query
     * @param  bool|null  $hasWarehouses
     * @return array
     */
    public function getSettlements(string $query, bool $hasWarehouses = null): array;

    /**
     * Get city using ref attribute
     *
     * @param  string  $ref
     * @return stdClass|null
     */
    public function getCity(string $ref): ?stdClass;

    /**
     * Get cities using findByString attribute
     *
     * @param  string  $query
     * @return array
     */
    public function getCities(string $query): array;

    /**
     * Get city warehouses using city ref
     *
     * @param  string  $cityRef
     * @return array
     */
    public function getCityWarehouses(string $cityRef): array;
}
