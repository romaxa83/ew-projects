<?php


namespace App\Contracts\Models;


interface HasModeration
{
    public function isModerated(): bool;

    public function shouldModerated(): bool;
}
