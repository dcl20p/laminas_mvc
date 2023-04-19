<?php

namespace Zf\Ext;

use Zf\Ext\LaminasRedisAdapter;

/**
 * Customize Laminas redis cache
 */
class LaminasRedisCache
{
    /**
     * namespace
     *
     * @var string|null
     */
    protected ?string $_myNamespace = null;

    /**
     * Time to live
     *
     * @var string|null
     */
    protected ?string $_myTTL = null;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $_instance = null;

    /**
     * Constructor
     *
     * @param LaminasRedisAdapter $cache
     */
    public function __construct(LaminasRedisAdapter $cache)
    {
        $this->_instance = $cache;
    }

    /**
     * Magic method to handle method calls dynamically
     *
     * @param string $method Name of the method to be called
     * @param array $arguments Arguments to be passed to the method
     * @return mixed The result of the method call
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (!method_exists($this, $method)) {
            $this->_instance->getOptions()
                ->setTtl($this->_myTTL)
                ->setNamespace($this->_myNamespace);
        }

        return $this->_instance->{$method}(...$arguments);
    }

    /**
     * Magic method to handle property retrieval dynamically
     *
     * @param string $key Name of the property to be retrieved
     * @return mixed The value of the property
     */
    public function __get(string $key): mixed
    {
        return $this->_instance->$key;
    }

    /**
     * Magic method to handle property assignment dynamically
     *
     * @param string $key Name of the property to be assigned
     * @param mixed $val Value to be assigned to the property
     */
    public function __set(string $key, mixed $val): void
    {
        $this->_instance->$key = $val;
    }

    /**
     * Sets the namespace to be used in cache storage
     *
     * @param string $namespace The namespace to be set
     * @return $this The current object for method chaining
     */
    public function setMyNamespace(string $namespace): self
    {
        $this->_myNamespace = $namespace;
        return $this;
    }

    /**
     * Sets the time to live (TTL) to be used in cache storage
     *
     * @param int $ttl The TTL to be set
     * @return $this The current object for method chaining
     */
    public function setMyTTL(int $ttl): self
    {
        $this->_myTTL = $ttl;
        return $this;
    }
}
