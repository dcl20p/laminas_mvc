<?php

namespace Zf\Ext;

use Laminas\Cache\Storage\Adapter\Redis as RedisCache;

/**
 * Customized Laminas Redis cache adapter
 */
class LaminasRedisAdapter
{
    /**
     * @var RedisCache
     */
    protected RedisCache $_instance;

    /**
     * Constructor
     *
     * @param RedisCache $cache The Laminas Redis cache instance
     */
    public function __construct(RedisCache $cache)
    {
        $this->_instance = $cache;
    }

    /**
     * Calls a method on the Laminas Redis instance
     *
     * @param string $method The method to call
     * @param array $args The arguments to pass to the method
     * @return mixed The result of the method call
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->_instance->{$method}(...$args);
    }

    /**
     * Gets a property from the Laminas Redis instance
     *
     * @param string $key The name of the property
     * @return mixed The value of the property
     */
    public function __get(string $key): mixed
    {
        return $this->_instance->{$key};
    }

    /**
     * Sets a property on the Laminas Redis instance
     *
     * @param string $key The name of the property
     * @param mixed $val The value to set the property to
     * @return void
     */
    public function __set(string $key, mixed $val): void
    {
        $this->_instance->{$key} = $val;
    }

    /**
     * Gets the namespace prefix for the Laminas Redis instance
     *
     * @return string The namespace prefix
     */
    public function getNamespacePrefix(): string
    {
        return $this->_instance->getOptions()->getNamespace()
            . $this->_instance->getOptions()->getNamespaceSeparator();
    }

    /**
     * Gets the Redis resource for the Laminas Redis instance
     *
     * @return Redis The Redis 
     */
    protected function getRedisResource()
    {
        return $this->_instance->getOptions()->getResourceManager()->getResource(
            $this->_instance->getOptions()->getResourceId()
        );
    }

    /**
     * Removes the namespace prefix from a Redis key
     *
     * @param string $key The Redis key to normalize
     * @return string The normalized Redis key
     */
    protected function normalizeRedisKey(string $key): string
    {
        return str_replace($this->getNamespacePrefix(), '', $key);
    }

    /**
     * Gets Redis keys that match a prefix
     *
     * @param string $prefix The prefix to match against
     * @return array The Redis keys that match the prefix
     */
    protected function getKeysByPrefix(string $prefix): array
    {
        return $this->getRedisResource()->keys($this->getNamespacePrefix() . $prefix . '*');
    }

    /**
     * Gets cache items that match a prefix
     *
     * @param string $prefix The prefix to match against
     * @return array The cache items that match the prefix
     */
    public function getItemsByPrefix(string $prefix): array
    {
        $keys = $this->getKeysByPrefix($prefix);
        $arrayItems = [];
        foreach ($keys as $key) {
            $key = $this->normalizeRedisKey($key);
            $arrayItems[$key] = $this->getItem($key);
        }
        return $arrayItems;
    }

    /**
     * Remove items with prefix
     *
     * @param string $prefix
     * @return bool
     */
    public function removeItemsByPrefix(string $prefix): bool
    {
        $keys = $this->getKeyByPrefix($prefix);

        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $this->removeItem($this->normalZfRedisKey($key));
        }

        return true;
    }

    /**
     * Change TTL of an item
     * @param string $key
     * @param int $ttl
     */
    public function changeTTLOfItemByKey(string $key, int $ttl = 604800): void
    {
        $this->getRedisResource()->expire($this->getNamespacePrefix() . $key, $ttl);
    }
    
    /**
     * Touch item
     * @param string $key
     * @return bool
     */
    public function touchItemIfExists(string $key): bool
    {
        if ($this->_instance->hasItem($key)) {
            $this->_instance->touchItem($key);
            return true;
        }
        return false;
    }
    
    /**
     * Explicitly disconnect
     */
    public function closeConnection(): void
    {
        $this->getRedisResource()->close();
        $this->getRedisResource()->__destruct();
    }

    /**
     * Is connected
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->getRedisResource()->isConnected();
    }
}
