<?php

namespace App\Contracts\Members;

interface HasPhoneNumber
{
    public function getPhoneString(): string;

    public function phoneVerified(): bool;
}
