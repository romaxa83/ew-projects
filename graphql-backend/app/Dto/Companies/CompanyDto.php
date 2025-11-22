<?php

namespace App\Dto\Companies;

class CompanyDto
{
    private string $name;

    private ?string $lang;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->lang = $args['lang'] ?? null;

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): void
    {
        $this->lang = $lang;
    }

    public function hasLang(): bool
    {
        return (bool)$this->lang;
    }
}
