<?php

namespace WezomCms\Core\Settings\Fields;

trait ValuesListContainerTrait
{
    protected $valuesList = [];

    /**
     * @param  iterable  $values
     * @return $this
     */
    public function setValuesList(iterable $values)
    {
        $this->valuesList = $values;

        return $this;
    }

    /**
     * Return array in format: [value => name, value2 => name2, ...]
     * @return iterable
     */
    public function getValuesList(): iterable
    {
        return $this->valuesList;
    }
}
