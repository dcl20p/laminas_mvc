<?php
namespace Zf\Ext\Utilities;

class CsrfToken
{
    const CSRF_TOKEN_FOLDER = 'csrf_tokens';
    const CSRF_TOKEN_EXT = 'csrf';
    const RAND_STRING = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const REDIS_NAMESPACE_PREFIX = 'CSRF_TOKEN_';

    private bool $_useOneTime = true;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Remove non-alphanumeric characters from string
     * @param string $str The string to be normalized
     * @return string The normalized string
     */

    private static function normalizeString(string $str): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $str);
    }

    /**
     * Generate a CSRF token key
     * @param array $opts Additional options to be included in the key generation
     * @return string The generated CSRF token key
     */
    private static function generateCsrfTokenKey(array $opts = []): string
    {
        return self::normalizeString(md5(json_encode([
            $_SERVER['REMOTE_ADDR'] ?? '',
            getHostByName(getHostName()),
            ...$opts
        ])));
    }

    /**
     * Get the file path of a CSRF token
     * @param string $userFolder The folder where the token is stored
     * @param string $filename The filename of the token
     * @param string|null $site The site where the token belongs to (optional)
     * @return string The file path of the token
     */
    private static function getCsrfTokenPath(string $userFolder, string $filename, ?string $site = null): string
    {
        return (defined('CSRF_TOKEN_DIR') ? implode('/', [
            CSRF_TOKEN_DIR,
            "{$userFolder}{$filename}." . self::CSRF_TOKEN_EXT
        ]) : implode('/', [
                DATA_PATH,
                self::CSRF_TOKEN_FOLDER,
                $site ?? APPLICATION_SITE,
                "{$userFolder}{$filename}." . self::CSRF_TOKEN_EXT
            ]));
    }
    /**
     * Get a Redis cache instance for the given namespace
     * @param string $namespace The namespace of the Redis cache
     * @param int|null $lifetime The lifetime of the Redis cache (optional)
     * @return \Laminas\Cache\Storage\StorageInterface The Redis cache instance
    */
    private function getRedisCache(string $namespace, ?int $lifetime = null): \Laminas\Cache\Storage\StorageInterface
    {
        return \Zf\Ext\CacheCore::_getRedisCaches($namespace, [
            'lifetime' => $lifetime,
            'namespace' => self::REDIS_NAMESPACE_PREFIX . $namespace
        ]);
    }

    /**
     * Normalizes a string by removing all characters except alphanumeric characters
     *
     * @param string $str The input string to normalize
     *
     * @return string The normalized string
     */
    private static function normalStr(string $str): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $str);
    }

    /**
     * Creates a unique CSRF token key for the given options array and returns the normalized string after hashing it using MD5
     *
     * @param array $opts An array of options to include in the hash
     *
     * @return string The normalized and hashed CSRF token key
     */
    private static function createCsrkKey(array $opts = []): string
    {
        return self::normalStr(md5(json_encode([
            $_SERVER['REMOTE_ADDR'] ?? '',
            getHostByName(getHostName()),
            ...$opts
        ])));
    }

    /**
     * Returns the path for the given user folder, filename, and site (optional), based on whether CSRF_TOKEN_DIR is defined or not.
     *
     * @param string $userFolder The user folder name
     * @param string $filename The filename to use
     * @param string|null $site (optional) The site name
     *
     * @return string The path to the CSRF token
     */
    private static function generalCsrkPath(string $userFolder, string $filename, ?string $site = null): string
    {
        return (defined('CSRF_TOKEN_DIR') ? implode('/', [
            CSRF_TOKEN_DIR,
            "{$userFolder}_{$filename}." . self::CSRF_TOKEN_EXT
        ]) : implode('/', [
                DATA_PATH,
                self::CSRF_TOKEN_FOLDER,
                $site ?? APPLICATION_SITE,
                "{$userFolder}_{$filename}." . self::CSRF_TOKEN_EXT
            ]));
    }

    /**
     * Saves a CSRF token to Redis cache with the given namespace, key, token and lifetime (optional)
     *
     * @param string $namespace The cache namespace
     * @param string $key The CSRF token key
     * @param string $token The CSRF token value
     * @param int $lifetime (optional) The lifetime of the cache
     *
     * @return bool True if the token was saved successfully, false otherwise
     */
    private function saveTokenByRedisAdapter(string $namespace, string $key, string $token, int $lifetime = 86400): bool
    {
        return $this->getRedisCache($namespace, $lifetime)->setItem($key, $token);
    }

    /**
     * Checks if the given CSRF token key is valid by looking up Redis cache for the key with the given namespace and lifetime (optional)
     *
     * @param string $namespace The cache namespace
     * @param string $key The CSRF token key
     * @param int $lifetime (optional) The lifetime of the cache
     *
     * @return bool True if the token is valid, false otherwise
     */

    private function isValidTokenByRedisAdapter(string $namespace, string $key, int $lifetime = 86400): bool
    {
        $redis = $this->getRedisCache($namespace, $lifetime);
        $hasItem = $redis->hasItem($key);
        $isValid = $hasItem
            && ($time = $redis->getItem($key)) > 0
            && (time() - $time) < $lifetime;

        if ($hasItem && $this->_useOneTime) {
            $redis->removeItem($key);
        }

        return $isValid;
    }

    private function clearTokenByRedisAdapter(string $namespace, string $key, int $lifetime = 86400): bool
    {
        return $this->getRedisCache($namespace, $lifetime)->removeItem($key);
    }

    public function generalCsrfToken(string $userFolder, array $unique = [], ?string $site = null, int $lifetime = 86400): string
    {
        $key = self::createCsrkKey($unique);
        $length = strlen($key);

        if (defined('REDIS_CONFIG')) {
            $this->saveTokenByRedisAdapter(
                $site ?? APPLICATION_SITE,
                "{$userFolder}_{$key}",
                time(),
                $lifetime
            );
        } else {
            @file_put_contents(
                self::generalCsrkPath($userFolder, $key, $site),
                base64_encode(gzcompress(time()))
            );
        }

        $char = $key[rand(0, $length - 1)];
        $charCode = ord($char);
        $key = str_replace($char, ':', $key);

        return $length . self::RAND_STRING[rand(0, 51)] . strrev($key) . $charCode;
    }

    /**
     * Checks if the token string is valid.
     *
     * @param string $token The token string to validate.
     *
     * @return bool|string Returns `true` if the token is valid, otherwise `false`.
     */
    public function validToken(string $token): bool|string
    {
        if (strlen($token) < 36) {
            return false;
        }

        if (!preg_match('/^(\d+)/', $token, $matches)) {
            return false;
        }

        $tkLength = (int) $matches[0];
        $length = strlen($tkLength) + 1;

        // Parse token.
        $key = chr((int) mb_substr($token, $length + $tkLength));
        $token = self::normalStr(str_replace(':', $key, strrev(mb_substr($token, $length, $tkLength))));

        return $token;
    }

    /**
     * Checks if the CSRF token is valid.
     *
     * @param string      $userFolder The user folder.
     * @param string|null $token      The CSRF token.
     * @param int         $lifetime   The lifetime of the token in seconds.
     * @param string|null $site       The site name.
     *
     * @return bool Returns `true` if the token is valid, otherwise `false`.
     */
    public function isValidCsrfToken(string $userFolder, ?string $token = null, int $lifetime = 86400, ?string $site = null): bool
    {
        $token = $this->validToken($token);

        if ($token === false) {
            return false;
        }

        if (defined('REDIS_CONFIG')) {
            return $this->isValidTokenByRedisAdapter(
                $site ?? APPLICATION_SITE,
                "{$userFolder}_{$token}",
                $lifetime
            );
        }

        $filePath = self::generalCsrkPath($userFolder, $token);

        if (file_exists($filePath)) {
            if ($this->_useOneTime) {
                $lastTime = filemtime($filePath);
                @unlink($filePath);

                if (is_numeric($lastTime) && $lastTime <= (time() - $lifetime)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Clears the CSRF token.
     *
     * @param string      $userFolder The user folder.
     * @param string|null $token      The CSRF token.
     * @param string|null $site       The site name.
     *
     * @return bool Returns `true` on success, otherwise `false`.
     */
    public function clearCsrfToken(string $userFolder, ?string $token = null, ?string $site = null): bool
    {
        $token = $this->validToken($token);

        if ($token === false) {
            return false;
        }

        if (defined('REDIS_CONFIG')) {
            return $this->clearTokenByRedisAdapter(
                $site ?? APPLICATION_SITE,
                "{$userFolder}_{$token}"
            );
        }

        $filePath = realpath(self::generalCsrkPath($userFolder, $token));

        if ($filePath && file_exists($filePath)) {
            return @unlink($filePath);
        }

        return false;
    }
}