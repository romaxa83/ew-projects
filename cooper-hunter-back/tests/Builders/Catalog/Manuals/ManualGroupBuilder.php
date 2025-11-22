<?php

namespace Tests\Builders\Catalog\Manuals;

use App\Models\Catalog\Manuals\ManualGroup;
use Database\Factories\Catalog\Manuals\ManualGroupTranslationFactory;
use Tests\Builders\BaseBuilder;

class ManualGroupBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return ManualGroup::class;
    }

    function getModelTranslationFactoryClass(): string
    {
        return ManualGroupTranslationFactory::class;
    }

    public function setShowCommercialCertified($value): self
    {
        $this->data['show_commercial_certified'] = $value;

        return $this;
    }
}

