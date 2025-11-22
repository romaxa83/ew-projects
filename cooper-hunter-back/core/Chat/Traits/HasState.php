<?php

namespace Core\Chat\Traits;

trait HasState
{
    public function reset(): static
    {
        foreach ($this->propertiesToResetState() as $property) {
            unset($this->{$property});
        }

        return $this;
    }

    abstract protected function propertiesToResetState(): array;
}
