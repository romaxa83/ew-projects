<?php

namespace Tests\Builders\Kamailio;

use App\IPTelephony\Services\Storage\Kamailio\LocationService;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Traits\FakerTrait;

class LocationBuilder
{
    use FakerTrait;

    protected Sip $sip;

    public function __construct(protected LocationService $service)
    {}

    public function setSip(Sip $model): self
    {
        $this->sip = $model;
        return $this;
    }

    function create(): void
    {
        $this->service->insert([
            'username' => $this->sip->number,
            'ruid' => $this->faker()->uuid
        ]);
    }
}
