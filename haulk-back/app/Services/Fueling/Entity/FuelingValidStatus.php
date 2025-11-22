<?php
namespace App\Services\Fueling\Entity;

interface FuelingValidStatus
{
    public function messages(): array;
    public function passes(): bool;
}
