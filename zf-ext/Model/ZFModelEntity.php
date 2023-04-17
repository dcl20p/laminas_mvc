<?php
namespace Zf\Ext\Model;

/**
 * Abstract class for ZF models.
 */
abstract class ZFModelEntity
{
    /**
     * Constructor.
     *
     * @param array $options An array of options to set as object properties.
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->fromArray($options);
        }
        $this->init();
    }

    /**
     * Initializes the object after construction.
     */
    public function init(): void
    {
    }

    /**
     * Sets a property on the object.
     *
     * @param mixed $name  The name of the property to set.
     * @param mixed $value The value to set on the property.
     *
     * @return self
     */
    public function __set(mixed $name, mixed $value)
    {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
        } else {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * Gets a property from the object.
     *
     * @param string $name The name of the property to get.
     *
     * @return mixed The value of the property.
     */
    public function __get(string $name): mixed
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            return $this->$name;
        }
    }

    /**
     * Converts the object to an array.
     *
     * @return array The object properties as an associative array.
     */
    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            $result[$key] = $this->__get($key);
        }
        return $result;
    }

    /**
     * Sets the object properties from an array of options.
     *
     * @param array $options An array of options to set as object properties.
     *
     * @return self
     */
    public function fromArray(array $options = []): self
    {
        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }
}

