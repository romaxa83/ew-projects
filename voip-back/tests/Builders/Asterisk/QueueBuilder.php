<?php

namespace Tests\Builders\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;

class QueueBuilder
{
    protected array $data = [];

    protected Department $department;

    public function __construct(protected QueueService $service)
    {}

    public function department(Department $model): self
    {
        $this->department = $model;
        return $this;
    }

    public function relativePeriodicAnnounce(string $value): self
    {
        $this->data['relative_periodic_announce'] = $value;
        return $this;
    }

    public function periodicAnnounce($value): self
    {
        $this->data['periodic_announce'] = $value;
        return $this;
    }

    public function periodicAnnounceFrequency($value): self
    {
        $this->data['periodic_announce_frequency'] = $value;
        return $this;
    }

    function create(): object
    {
        $this->service->create($this->department, $this->data);

        $this->data = [];

        return $this->service->getBy('uuid', $this->department->guid);
    }
}
