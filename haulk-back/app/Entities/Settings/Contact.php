<?php

namespace App\Entities\Settings;

use Stringable;

class Contact implements Stringable
{
    protected array $contact;

    public function __construct(array $contact)
    {
        $this->contact = $contact;
    }

    public function __toString(): string
    {
        if ($this->getName()) {
            return sprintf('%s: %s', $this->getName(), $this->getPhone());
        }

        return $this->getPhone();
    }

    public function getName(): ?string
    {
        return $this->contact['name'] ?? null;
    }

    public function getPhone(): ?string
    {
        return phone_format($this->contact['number'] ?? '');
    }
}
