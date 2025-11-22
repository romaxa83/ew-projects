<?php

namespace App\Entities\Warranty;

use JsonException;

class WarrantyUserInfo
{
    /**
     * Determine if user or technician info is given
     */
    public bool $is_user;

    public string $first_name;
    public string $last_name;
    public string $email;
    public null|string $company_name;
    public null|string $company_address;

    public static function make(array $arr, bool $isUser): self
    {
        $self = new self();

        $self->is_user = $isUser;

        $self->first_name = $arr['first_name'];
        $self->last_name = $arr['last_name'];
        $self->email = $arr['email'];
        $self->company_name = $arr['company_name'] ?? null;
        $self->company_address = $arr['company_address'] ?? null;

        return $self;
    }

    /** @throws JsonException */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
