<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Protocol;
use Database\Factories\Commercial\Commissioning\ProtocolTranslationFactory;
use Tests\Builders\BaseBuilder;

class ProtocolBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return Protocol::class;
    }

    function getModelTranslationFactoryClass(): string
    {
        return ProtocolTranslationFactory::class;
    }

    public function setType($value): self
    {
        $this->data['type'] = $value;

        return $this;
    }

    public function setSort($value): self
    {
        $this->data['sort'] = $value;

        return $this;
    }
}
