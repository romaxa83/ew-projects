<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation;

final class CalculationPipeline
{
    private array $handlers = [];

    public function addHandlers(array $handlers): self
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    public function addHandler(CalculationHandler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function process(CalcPayload $payload): CalcPayload
    {
        foreach ($this->handlers as $handler) {
            $payload = $handler->handle($payload);
        }

        return $payload;
    }
}
