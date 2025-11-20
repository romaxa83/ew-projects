<?php

namespace WezomCms\Core\Filter;

use Cookie;
use Illuminate\Http\Request;
use WezomCms\Core\Contracts\Filter\FilterStateStorageInterface;

class CookieStateStorage implements FilterStateStorageInterface
{
    protected const PREFIX = 'filter_';

    protected const LIFE_TIME = 525600; // 1 year

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param  Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Checks if storage has any parameters by key.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->request->hasCookie($this->generateKey($key));
    }

    /**
     * Deletes saved parameters by key.
     *
     * @param  string  $key
     * @return FilterStateStorageInterface
     */
    public function forget(string $key): FilterStateStorageInterface
    {
        Cookie::queue(Cookie::forget($this->generateKey($key)));

        return $this;
    }

    /**
     * Save/update parameters by key.
     *
     * @param  string  $key
     * @param  array  $params
     * @return FilterStateStorageInterface
     */
    public function set(string $key, array $params): FilterStateStorageInterface
    {
        Cookie::queue($this->generateKey($key), json_encode($params), static::LIFE_TIME);

        return $this;
    }

    /**
     * Get stored parameters.
     *
     * @param  string  $key
     * @return array
     */
    public function get(string $key): array
    {
        return json_decode($this->request->cookie($this->generateKey($key), '[]'), true);
    }

    /**
     * @param  string  $key
     * @return string
     */
    protected function generateKey(string $key): string
    {
        return static::PREFIX . $key;
    }
}
