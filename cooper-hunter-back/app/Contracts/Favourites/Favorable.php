<?php

namespace App\Contracts\Favourites;

interface Favorable
{
    public function getFavorableType(): string;

    public function getId(): int;
}
