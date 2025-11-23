<?php

namespace WezomCms\Core\Contracts;

/**
 * Interface SettingsInterface
 * @package WezomCms\Core\Contracts
 */
interface SettingsInterface
{
    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return SettingsInterface
     */
    public function set(string $name, $value): SettingsInterface;

    /**
     * @param  string  $name
     * @param  mixed|null  $default
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param  string  $name
     * @return SettingsInterface
     */
    public function forget(string $name): SettingsInterface;

    /**
     * Fresh all cached data
     */
    public function fresh();
}
