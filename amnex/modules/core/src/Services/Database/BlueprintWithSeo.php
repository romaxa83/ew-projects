<?php

declare(strict_types=1);

namespace Wezom\Core\Services\Database;

use Illuminate\Database\Schema\Blueprint;

readonly class BlueprintWithSeo
{
    public function __construct(private Blueprint $blueprint)
    {
    }

    public function h1(string $name = 'seo_h1', int $length = 255, bool $nullable = true): self
    {
        return $this->addStringField($name, $length, $nullable);
    }

    private function addStringField(string $name, int $length, bool $nullable): self
    {
        $this->blueprint->string($name, $length)->nullable($nullable);

        return $this;
    }

    public function title(string $name = 'seo_title', int $length = 255, bool $nullable = true): self
    {
        return $this->addStringField($name, $length, $nullable);
    }

    public function description(string $name = 'seo_description', int $length = 1024, bool $nullable = true): self
    {
        return $this->addStringField($name, $length, $nullable);
    }

    public function text(string $name = 'seo_text', bool $nullable = true): self
    {
        return $this->addTextField($name, $nullable);
    }

    private function addTextField(string $name, bool $nullable): self
    {
        $this->blueprint->text($name)->nullable($nullable);

        return $this;
    }
}
