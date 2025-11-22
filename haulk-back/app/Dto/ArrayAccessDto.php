<?php


namespace App\Dto;


abstract class ArrayAccessDto implements \ArrayAccess
{

    public function offsetSet($offset, $value) {
        if (property_exists($offset,$this)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->{$offset});
    }

    public function offsetUnset($offset) {
        $this->{$offset} = null;
    }

    public function offsetGet($offset) {
        return property_exists($offset,$this) ? $this->{$offset} : null;
    }
}
