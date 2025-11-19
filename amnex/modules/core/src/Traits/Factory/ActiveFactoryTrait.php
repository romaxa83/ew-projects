<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Factory;

use Wezom\Core\Enums\ActiveTypeEnum;

trait ActiveFactoryTrait
{
    public function active(bool $active = true): static
    {
        return $this->state(compact('active'));
    }

    public function disabled(): static
    {
        return $this->set('active', ActiveTypeEnum::DISABLED);
    }
}
