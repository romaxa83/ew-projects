<?php

namespace WezomCms\Catalog\Filter\Exceptions;

class IncorrectSortException extends \Exception
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @param  mixed  $parameter
     * @param  mixed  $items
     * @return $this
     */
    public function setCorrectSort($parameter, $items = null): IncorrectSortException
    {
        if (is_array($parameter)) {
            $this->parameters = $parameter;
        } else {
            $items = is_array($items) ? $items : [$items];
            $this->parameters = [$parameter => $items];
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
