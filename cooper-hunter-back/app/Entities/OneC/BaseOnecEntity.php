<?php

namespace App\Entities\OneC;

abstract class BaseOnecEntity
{
    public bool $success = true;
    public ?string $error = null;

    protected function setDefaults(array $data): self
    {
        $this->success = $data['successful'];
        $this->error = $data['error'] ?? null;

        return $this;
    }
}