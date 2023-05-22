<?php
namespace Zf\Ext;

use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\Cache\Storage\Plugin\ExceptionHandler;
use Laminas\Cache\Storage\Plugin\PluginOptions;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use Doctrine\ORM\EntityManager;
use \Zf\Ext\LaminasRedisAdapter;
use \Zf\Ext\LaminasRedisCache;

abstract class CacheCore
{
    /**
     * Location of cache
     *
     * @var string
     */
    protected static string $_path = '/cache';

    /**
     * The cache core.
     *
     * @var StorageInterface|null
     */
    protected static $_cacheCore = null;

    /**
     * The Doctrine entity manager adapter.
     *
     * @var EntityManager|null
     */
    protected static ?EntityManager $_dAdapter = null;

    /**
     * Check whether the given directory exists and create it if it doesn't.
     *
     * @param string $namespace
     * @param string|array $dir The directory path to check or an array with 'path' key-value.
     * @return string The real path of the directory.
     */
    protected static function _checkDir(string $namespace = '', string|array $dir = ''): string
    {
        if (is_array($dir)) {
            $path = $dir['path'];
            if (!is_dir($path)) {
                @mkdir($path);
            }
        } else {
            if (!is_dir(DATA_PATH . self::$_path . '/' . $dir)) {
                @mkdir(DATA_PATH . self::$_path . '/' . $dir);
            }
            $path = DATA_PATH . self::$_path . "/{$dir}/{$namespace}";
            if (!is_dir($path)) {
                @mkdir($path);
            }
        }
        return realpath($path);
    }

    /**
     * Get the cache storage.
     *
     * @param string $cacheKey The cache key.
     * @param array $opts The cache options.
     *
     * @return StorageInterface The cache storage.
     */
    public static function _getCaches(string $cacheKey, array $opts = []): StorageInterface
    {
        if (defined('REDIS_CONFIG')) {
            return self::_getRedisCaches($cacheKey, $opts);
        }

        // Life time
        $lifetime = $opts['lifetime'] ?? 86400; // 86400 = 1 day
        if (false === $opts['lifetime']) {
            $lifetime = null;
        }

        $namespace = 'zfcache';
        if (isset($opts['namespace'])) {
            $namespace = $opts['namespace'];
        }

        $path = self::_checkDir($namespace, $opts['path']);

        if (null === self::$_cacheCore || empty(self::$_cacheCore[$cacheKey])) {
            if (class_exists(StorageFactory::class)) {
                self::$_cacheCore[$cacheKey] = StorageFactory::factory([
                    'adapter' => [
                        'name' => 'filesystem',
                        'options' => [
                            'namespace' => $namespace,
                            'ttl' => $lifetime,
                            'cache_dir' => $path
                        ]
                    ],
                    'plugins' => [
                        // Don't throw exceptions on cache errors.
                        'exception_handler' => [
                            'throw_exceptions' => false
                        ],
                        'Serializer'
                    ]
                ]);
            }
        }

        return self::$_cacheCore[$cacheKey];
    }

    /**
     * Get the Redis cache storage.
     *
     * @param string $cacheKey The cache key.
     * @param array $opts The cache options.
     *
     * @return StorageInterface The Redis cache storage.
     */
    public static function _getRedisCaches(string $cacheKey, array $opts = [])
    {
        $lifetime = $opts['lifetime'] ? $opts['lifetime'] : 86400; // 86400 = 1 days
        if (false === $opts['lifetime']) $lifetime = 0;

        // Laminas cache Version 1
        if (!isset(self::$_cacheCore[$cacheKey])) {
            self::$_cacheCore[$cacheKey] = self::createRedisCache($cacheKey, $opts, $lifetime);
        } else {
            // Re-use connection
            self::$_cacheCore[$cacheKey]->getOptions()
                // Change time to live
                ->setTtl($lifetime)
                // Change name space
                ->setNamespace(
                    $opts['namespace'] ?? DOMAIN_NAME
                );
        }
        return (new LaminasRedisCache(self::$_cacheCore[$cacheKey]))
            ->setMyNamespace($opts['namespace'] ?? DOMAIN_NAME)
            ->setMyTTL($lifetime);
    }
    /**
     * Create Redis cache storage instance
     * 
     * @param string $cacheKey The cache key
     * @param array $options The cache options
     * @param int|bool $lifetime The cache lifetime
     * @return StorageInterface The Redis cache storage instance
     */
    public static function createRedisCache(string $cacheKey, array $options = [], int|bool $lifetime = false): mixed
    {
        $adapterName = ZFRedisAdapter::class;
        $namespace = $options['namespace'] ?? 'redis_cache';
        $ttl = $lifetime;

        $adapterOptions = array_merge(compact('namespace', 'ttl'), REDIS_CONFIG);
        $plugins = [
            'exception_handler' => [
                'throw_exceptions' => true
            ],
            'Serializer'
        ];

        if (class_exists('Laminas\Cache\StorageFactory')) {
            return StorageFactory::factory([
                'adapter' => [
                    'name' => $adapterName,
                    'options' => $adapterOptions,
                ],
                'plugins' => $plugins,
            ]);
        }

        unset($options['lifetime']);

        $redisAdapter = new Redis($adapterOptions);
        $redisAdapter->addPlugin(
            (new ExceptionHandler())
                ->setOptions(new PluginOptions([
                    'throw_exceptions' => true
                ]))
        )
            ->addPlugin(new Serializer());

        return new LaminasRedisAdapter($redisAdapter);
    }
}