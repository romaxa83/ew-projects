<?php

namespace WezomCms\Core\Traits;

use Exception;
use Lang;
use LaravelLocalization;
use Route;

trait LangSwitchingGenerator
{
    /**
     * @param $model
     * @param  string  $transKeyName  - Route name alias or trans key name.
     * @param  array  $attributes
     * @param  bool  $justPublished
     * @param  string  $publishKey
     */
    public function setLangSwitchers(
        $model,
        string $transKeyName,
        array $attributes = ['slug' => 'slug'],
        bool $justPublished = true,
        string $publishKey = 'published'
    ) {
        if (!method_exists($model, 'translations')) {
            return;
        }

        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());

        $query = $model->translations();

        if ($justPublished && $model->isTranslationAttribute($publishKey)) {
            $query->where($publishKey, true);
        }

        $query->whereIn('locale', $supportedLocales)
            ->each(function ($row) use ($model, $transKeyName, $attributes) {
                $modifyAttributes = $this->buildAttributes($row, $model, $attributes);

                if (Lang::hasForLocale($transKeyName, $row->locale)) {
                    $url = LaravelLocalization::getURLFromRouteNameTranslated(
                        $row->locale,
                        $transKeyName,
                        $modifyAttributes
                    );
                } elseif (Route::has($transKeyName)) {
                    $url = LaravelLocalization::getLocalizedURL($row->locale, route($transKeyName, $modifyAttributes));
                } else {
                    throw new Exception("Route not defined for '{$transKeyName}' key");
                }

                if ($queryString = app('request')->getQueryString()) {
                    $url .= '?' . $queryString;
                }

                LaravelLocalization::addSwitchingLink($row->locale, $url);
            });
    }

    /**
     * @param  string  $route
     * @param  array  $params
     * @throws Exception
     */
    public function setLangSwitchersByRoute(string $route, array $params = [])
    {
        foreach (array_keys(app('locales')) as $locale) {
            if (Lang::hasForLocale($route, $locale)) {
                $url = LaravelLocalization::getURLFromRouteNameTranslated(
                    $locale,
                    $route,
                    $params
                );
            } elseif (Route::has($route)) {
                $url = LaravelLocalization::getLocalizedURL($locale, route($route, $params));
            } else {
                throw new Exception("Route not defined for '{$route}' key");
            }

            if ($queryString = app('request')->getQueryString()) {
                $url .= '?' . $queryString;
            }

            LaravelLocalization::addSwitchingLink($locale, $url);
        }
    }

    /**
     * @param $row
     * @param $model
     * @param  array  $attributes
     * @return array
     */
    protected function buildAttributes($row, $model, array $attributes = [])
    {
        foreach ($attributes as $key => $param) {
            if ($key === 'not-model') {
                foreach ($param as $paramKey => $paramValue) {
                    $attributes[$paramKey] = $paramValue;
                }
                unset($attributes['not-model']);
            } elseif (starts_with($param, 'model.')) {
                $attributes[$key] = $model->{mb_substr($param, 6)};
            } else {
                $attributes[$key] = $row->{$param};
            }
        }

        return $attributes;
    }
}
