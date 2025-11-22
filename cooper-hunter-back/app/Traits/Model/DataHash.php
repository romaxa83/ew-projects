<?php

namespace App\Traits\Model;

/**
 * @see DataHash::equalsHash()
 */
trait DataHash
{
    public function equalsHash(string $hash): bool
    {
        return $this->hash === $hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        $this->save();

        return $this;
    }
}


