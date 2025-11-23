<?php

namespace WezomCms\Catalog\Filter\UrlBuilders;

use Illuminate\Support\Str;
use WezomCms\Catalog\Filter\Contracts\UrlBuilderInterface;

class UrlBuilder extends AbstractUrlBuilder implements UrlBuilderInterface
{
    protected const SPECS_DELIMITER = ';';
    protected const KEY_VAL_DELIMITER = '=';
    protected const VALUES_DELIMITER = ',';

    /**
     * @var string
     */
    protected string $routeName;

    /**
     * @var string
     */
    protected string $emptyRouteName;

    /**
     * @var array
     */
    protected array $routeParameters = [];

    /**
     * UrlBuilder constructor.
     * @param  string|null  $routeName
     * @param  string|null  $emptyRouteName
     */
    public function __construct(?string $routeName = null, ?string $emptyRouteName = null)
    {
        parent::__construct();

        if ($routeName !== null) {
            $this->setRouteName($routeName);
        }

        if ($emptyRouteName !== null) {
            $this->setEmptyRouteName($emptyRouteName);
        }
    }

    /**
     * @param  string  $name
     * @return UrlBuilderInterface
     */
    public function setRouteName(string $name): UrlBuilderInterface
    {
        $this->routeName = $name;

        return $this;
    }

    /**
     * @param  string  $name
     * @return UrlBuilderInterface
     */
    public function setEmptyRouteName(string $name): UrlBuilderInterface
    {
        $this->emptyRouteName = $name;

        return $this;
    }

    /**
     * @param  array  $parameters
     * @return UrlBuilder
     */
    public function setRouteParameters(array $parameters = []): UrlBuilder
    {
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * @return UrlBuilderInterface
     */
    public function start(): UrlBuilderInterface
    {
        $this->parameters = Str::of($this->request->route('filter'))
            ->explode(static::SPECS_DELIMITER)
            ->filter()
            ->mapWithKeys(function ($value) {
                $parts = explode(static::KEY_VAL_DELIMITER, $value, 2);

                $values = Str::of(array_get($parts, 1, ''))
                    ->explode(static::VALUES_DELIMITER)
                    ->filter()
                    ->all();

                return [array_get($parts, 0) => $values];
            })
            ->filter();

        return $this;
    }

    /**
     * @param  iterable  $parameters
     * @return string
     */
    public function generateUrl(iterable $parameters = []): string
    {
        $filterUrl = collect($this->prepareParameters($parameters))
            ->map(fn ($items, $key) => $key . static::KEY_VAL_DELIMITER . implode(static::VALUES_DELIMITER, $items))
            ->values()
            ->implode(static::SPECS_DELIMITER);

        if ($filterUrl) {
            return $this->appendQueryString(
                route($this->routeName, array_merge($this->routeParameters, ['filter' => $filterUrl]))
            );
        }

        return $this->appendQueryString(route($this->emptyRouteName, $this->routeParameters));
    }
}
