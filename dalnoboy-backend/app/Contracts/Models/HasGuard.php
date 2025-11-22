<?php


namespace App\Contracts\Models;


interface HasGuard
{
    public function getGuard(): string;

    public function getId(): int;
}
