<?php

namespace WezomCms\Core\Traits;

trait LocalizedRequestTrait
{
    /**
     * @param  array  $rules
     * @param  array  $notLocalized
     * @return array
     */
    protected function localizeRules(array $rules, array $notLocalized = []): array
    {
        $items = [];

        foreach (app('locales') as $locale => $language) {
            foreach ($rules as $field => $rule) {
                $items[$locale . '.' . $field] = str_replace('{locale}', $locale, $rule);
            }
        }

        return $notLocalized ? array_merge($notLocalized, $items) : $items;
    }

    /**
     * @param  array  $attributes
     * @param  array  $notLocalized
     * @return array
     */
    protected function localizeAttributes(array $attributes, array $notLocalized = []): array
    {
        $items = [];

        foreach (app('locales') as $locale => $language) {
            foreach ($attributes as $key => $name) {
                $items[$locale . '.' . $key] = sprintf('%s (%s)', $name, $language);
            }
        }

        return $notLocalized ? array_merge($notLocalized, $items) : $items;
    }
}
