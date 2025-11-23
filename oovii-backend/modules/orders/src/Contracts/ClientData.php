<?php

namespace WezomCms\Orders\Contracts;

interface ClientData
{
    public function getName(): string;

    public function getSurname(): string;

    public function getFullName(): string;

    public function getPhone(): string;

    public function getEmail(): ?string;
}
