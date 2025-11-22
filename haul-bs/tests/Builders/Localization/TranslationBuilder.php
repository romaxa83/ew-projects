<?php

namespace Tests\Builders\Localization;

use App\Foundations\Modules\Localization\Models\Translation;
use Tests\Builders\BaseBuilder;

class TranslationBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Translation::class;
    }

    function place(string $value): self
    {
        $this->data['place'] = $value;

        return $this;
    }

    function key(string $value): self
    {
        $this->data['key'] = $value;

        return $this;
    }

    function lang(string $value): self
    {
        $this->data['lang'] = $value;

        return $this;
    }

    function text(string $value): self
    {
        $this->data['text'] = $value;

        return $this;
    }
}
