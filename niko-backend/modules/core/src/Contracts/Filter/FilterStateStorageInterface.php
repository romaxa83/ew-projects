<?php

namespace WezomCms\Core\Contracts\Filter;

interface FilterStateStorageInterface
{
    /**
     * Checks if storage has any parameters by key.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Deletes saved parameters by key.
     *
     * @param  string  $key
     * @return FilterStateStorageInterface
     */
    public function forget(string $key): FilterStateStorageInterface;

    /**
     * Save/update parameters by key.
     *
     * @param  string  $key
     * @param  array  $params
     * @return FilterStateStorageInterface
     */
    public function set(string $key, array $params): FilterStateStorageInterface;

    /**
     * Get stored parameters.
     *
     * @param  string  $key
     * @return array
     */
    public function get(string $key): array;
}
