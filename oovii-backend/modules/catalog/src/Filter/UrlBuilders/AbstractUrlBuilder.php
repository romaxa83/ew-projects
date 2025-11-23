<?php

namespace WezomCms\Catalog\Filter\UrlBuilders;

use Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use WezomCms\Catalog\Filter\Contracts\UrlBuilderInterface;

abstract class AbstractUrlBuilder
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Collection
     */
    protected Collection $parameters;

    /**
     * AbstractUrlBuilder constructor.
     */
    public function __construct()
    {
        $this->parameters = collect();
    }

    /**
     * @param  Request  $request
     * @return UrlBuilderInterface|self
     */
    public function setRequest(Request $request): UrlBuilderInterface
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param  array|null  $keys
     * @return iterable
     */
    public function getParameters(array $keys = null): iterable
    {
        if (null !== $keys) {
            return $this->parameters->only($keys)->all();
        }

        return $this->parameters->all();
    }

    /**
     * @param $key
     * @param $value
     * @return UrlBuilderInterface|self
     */
    public function add($key, $value): UrlBuilderInterface
    {
        $this->parameters->put($key, $value);

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return $this->parameters->has($key);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function present($key, $value): bool
    {
        return in_array($value, $this->parameters->get($key, []));
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->parameters->get($key, $default);
    }

    /**
     * Extract first value.
     *
     * @param $key
     * @param  null  $default
     * @return mixed
     */
    public function first($key, $default = null)
    {
        return array_first($this->parameters->get($key, []), null, $default);
    }

    /**
     * @param $key
     * @return bool
     */
    public function remove($key): bool
    {
        $this->parameters->forget($key);

        return true;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function autoBuild($key, $value): string
    {
        return $this->present($key, $value)
            ? $this->buildUrlWithout($key, $value)
            : $this->buildUrlWith($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function buildUrlWith($key, $value): string
    {
        $parameters = $this->parameters->all();

        if (!isset($parameters[$key])) {
            $parameters[$key] = [];
        }

        $parameters[$key][] = $value;

        return $this->generateUrl($parameters);
    }

    /**
     * @param  string|array  $key
     * @param  mixed|null  $value
     * @return string
     */
    public function buildUrlWithout($key, $value = null): string
    {
        $parameters = $this->parameters->all();

        if (is_array($key)) {
            $remove = $key;
        } else {
            $remove[$key] = $value;
        }

        foreach ($remove as $removeKey => $removeValue) {
            if (isset($parameters[$removeKey])) {
                foreach ((array) $removeValue as $removal) {
                    $position = array_search($removal, $parameters[$removeKey]);
                    if ($position !== false) {
                        unset($parameters[$removeKey][$position]);
                    }
                }
            }
        }

        return $this->generateUrl($parameters);
    }

    /**
     * @param  iterable  $parameters
     * @return array
     */
    protected function prepareParameters(iterable $parameters): array
    {
        return collect($parameters)
            ->map(fn($items) => collect((array)$items)->filter()->sort()->values()->all())
            ->filter()
            ->sortKeys()
            ->all();
    }

    /**
     * @param  string  $url
     * @param  array  $queryParams
     * @return string
     */
    protected function appendQueryString(string $url, array $queryParams = []): string
    {
        $query = array_merge($queryParams, $this->request->query());

        return $url . ($query ? '?' . Arr::query($query) : '');
    }
}
