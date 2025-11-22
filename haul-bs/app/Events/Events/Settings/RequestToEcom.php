<?php

namespace App\Events\Events\Settings;

use Illuminate\Database\Eloquent\Collection;

class RequestToEcom
{
    public function __construct(
        protected Collection $settings,
    )
    {}

    public function getData(): Collection
    {
        return $this->settings;
    }
}
