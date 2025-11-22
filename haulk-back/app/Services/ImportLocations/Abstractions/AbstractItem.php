<?php


namespace App\Services\ImportLocations\Abstractions;


abstract class AbstractItem
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var
     */
    public $attributes = [];

    /**
     * @var array
     */
    private $data;
    /**
     * @var
     */
    protected $synonyms;
    /**
     * @var mixed
     */
    public $name;

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->{$name} ?? $default;
    }


    public function __construct(int $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
        $this->parse();
    }

    /**
     *
     */
    public function parse(): void
    {
        foreach ($this->data as $key => $value) {
            $this->setAttribute($key, trim($value));
        }
    }

    /**
     * @param string $propertyName
     * @param $value
     */
    public function setAttribute(string $propertyName, $value): void
    {
        if (isset($this->synonyms[$propertyName])) {
            $this->{$this->synonyms[$propertyName]} = $value;
        }
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

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
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