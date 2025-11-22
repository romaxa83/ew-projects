<?php

namespace App\Traits\Requests;

trait ContactTransformerTrait
{
    protected function transform(string $contact): void
    {
        $phone = $this->input($contact . '.phone');
        if (strlen($phone) > config('orders.contacts.max_phone_prefix_length')) {
            return;
        }

        $attributes = $this->all();
        if (isset($attributes[$contact]['phone'])) {
            $attributes[$contact]['phone'] = null;
            $this->merge($attributes);
        }
    }

    protected function transformPhoneAttribute(string $attributeName): void
    {
        $phone = $this->input($attributeName);
        if (strlen($phone) > config('orders.contacts.max_phone_prefix_length')) {
            return;
        }

        $attributes = $this->all();
        if (isset($attributes[$attributeName])) {
            $attributes[$attributeName] = null;
            $this->merge($attributes);
        }
    }
}
