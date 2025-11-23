<?php

namespace WezomCms\Core\Traits;

use Arr;

trait Agree
{
    /**
     * @var bool
     */
    public bool $agree = true;

    /**
     * @param $rules
     * @param $messages
     * @param $attributes
     * @return array
     */
    protected function providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes)
    {
        [$rules, $messages, $attributes] = parent::providedOrGlobalRulesMessagesAndAttributes(
            $rules,
            $messages,
            $attributes
        );

        if (!array_key_exists('agree', $rules)) {
            $rules['agree'] = 'required|accepted';
        }

        if (!Arr::first($messages, fn ($value, $key) => str_starts_with($key, 'agree'))) {
            $messages['agree.*'] = __('cms-core::site.You must accept privacy terms');
        }

        return [$rules, $messages, $attributes];
    }

    /**
     * @param  array|null  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * @return array
     */
    public function validate($rules = null, $messages = [], $attributes = [])
    {
        return  Arr::except(parent::validate($rules, $messages, $attributes), 'agree');
    }

    /**
     * @param ...$properties
     */
    public function reset(...$properties)
    {
        $properties[] = 'agree';

        parent::reset($properties);
    }
}
