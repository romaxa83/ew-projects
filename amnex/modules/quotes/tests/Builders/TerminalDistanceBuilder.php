<?php

namespace Wezom\Quotes\Tests\Builders;

use Wezom\Core\Tests\Builders\BaseBuilder;
use Wezom\Quotes\Models\TerminalDistance;

class TerminalDistanceBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return TerminalDistance::class;
    }

    public function distance_as_mile(float $value): self
    {
        $this->data['distance_as_mile'] = $value;

        return $this;
    }
}
