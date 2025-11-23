<?php

namespace WezomCms\Imports\Templates;

abstract class TemplateAbstract
{
    public $attributes = [];

    public $message = [];

    protected $data;

    protected $synonyms = [];

    public static $requiredColumns = [];

    public function parse(): void
    {
        foreach ($this->data as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setAttribute(string $propertyValue, $value): void
    {
        if (isset($this->synonyms[$propertyValue])) {
            $this->{$this->synonyms[$propertyValue]} = $value;
        }
    }

    public function isValid(): bool
    {
        return !$this->isNotValid();
    }

    public function isNotValid(): bool
    {
        return false;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->{$name} ?? $default;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        } elseif (property_exists($this, $name)) {
            return $this->{$name};
        }
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $methodName = 'set' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
        } elseif (property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }
}

