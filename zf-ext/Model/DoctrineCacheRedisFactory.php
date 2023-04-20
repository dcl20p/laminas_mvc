<?php
namespace Zf\Ext\Model;

use Psr\Container\ContainerInterface;
use Redis;
use Doctrine\Common\Cache\RedisCache;

/**
 * Factory to create Doctrine Redis Cache instance
 */
class DoctrineCacheRedisFactory
{
    /**
     * Creates a Doctrine Redis Cache instance
     *
     * @param ContainerInterface $container The container instance
     * @param string $requestedName The name of the requested cache instance
     * @param array|null $options The options for the Redis connection
     *
     * @return RedisCache The RedisCache instance
     *
     * @throws \RedisException If the Redis connection fails
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): RedisCache {
        $redis = new Redis();

        try {
            $redis->connect(
                REDIS_CONFIG['server']['host'],
                REDIS_CONFIG['server']['port'],
                $options['timeout'] ?? 0.0
            );
        } catch (RedisException $e) {
            throw new \RedisException("Failed to connect to Redis server: {$e->getMessage()}");
        }

        if (isset(REDIS_CONFIG['password'])) {
            $redis->auth(REDIS_CONFIG['password']);
        }

        $redis->select(REDIS_CONFIG['database'] ?? 0);
        $redis->setOption(Redis::OPT_PREFIX, 'DOCTRINE_CACHE:');

        $cache = new RedisCache();
        $cache->setRedis($redis);

        return $cache;
    }
}
