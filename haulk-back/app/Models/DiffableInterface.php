<?php

namespace App\Models;


interface DiffableInterface
{
    public function getAttributesForDiff(): array;

    /**
     * @return DiffableInterface[]
     */
    public function getRelationsForDiff(): array;
}
