<?php

namespace App\Dto;

trait HasArrayAccessDto
{
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }
        return $this->{$offset};
    }

    public function offsetExists($offset): bool
    {
        if (!property_exists($this, $offset)) {
            return false;
        }

        if (!isset($this->{$offset}) || $this->{$offset} === null) {
            return false;
        }

        return true;
    }

    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            return;
        }
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            return;
        }
        unset($this->{$offset});
    }
}
