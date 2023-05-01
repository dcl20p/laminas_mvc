<?php
/**
 * Zend Framework (http://github.com/zendframework/zf2)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc.
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Zf\Ext\Utilities\CsrfToken;

/**
 * Class ZfCsrfToken
 * @package Zf\Ext\Controller
 */
class ZfCsrfToken extends AbstractPlugin
{
    const SERVICE_ALIAS = 'zfCsrfToken';

    /**
     * @var CsrfToken|null
     */
    protected static  ?CsrfToken $csrfToken = null;

    /**
     * @var string
     */
    protected static string $userKey = '';

    /**
     * ZfCsrfToken constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!self::$csrfToken) {
            self::$csrfToken = new CsrfToken();
        }

        if ($container->has(AuthenticationService::class)) {
            $authen = $container->get(AuthenticationService::class)->getIdentity();
            $configs = $container->get('config');

            if (!empty($authen)) {
                if (isset($configs['csrf_token']) && isset($configs['csrf_token'][APPLICATION_SITE])) {
                    self::$userKey = $authen->{$configs['csrf_token'][APPLICATION_SITE]} ?? '';
                } else {
                    self::$userKey = $authen->user_code ?? $authen->admin_code ?? '';
                }
            } else {
                self::$userKey = '';
            }

            unset($configs, $authen);
        }
    }

    /**
     * @param bool $useOneTime
     * @return $this
     */
    public function __invoke(bool $useOneTime = true): self
    {
        self::$csrfToken->_useOneTime = $useOneTime;

        return $this;
    }

    /**
     * Create CSRF token
     *
     * @param array $unique
     * @param string|null $userFolder
     * @param string|null $site
     * @param int $lifetime
     * @return string
     */
    public function generateCsrfToken(
        array $unique = [], 
        ?string $userFolder = null, 
        ?string $site = null, 
        int $lifetime = 86400): string
    {
        return self::$csrfToken->generateCsrfToken(
            $userFolder ?? self::$userKey, $unique, $site, $lifetime
        );
    }

    /**
     * Get token from header of request
     *
     * @param string|null $token
     * @return string|null
     */
    protected function getToken(?string $token = null): ?string
    {
        $token = $token ?? $this->getController()->params()->fromHeader('Csrf-Token', '');

        if ($token && is_object($token)) {
            $token = $token->getFieldValue();
        }

        return $token;
    }

    /**
     * Check CSRF token
     *
     * @param string|null $token
     * @param string|null $userFolder
     * @param int $lifetime
     * @param string|null $site
     * @return bool true if token is valid
     */
    public function isValidCsrfToken(
        ?string $token = null, 
        ?string $userFolder = null, 
        int $lifetime = 86400, 
        ?string $site = null): bool
    {
        $token = $this->getToken($token);
        return self::$csrfToken->isValidCsrfToken(
            $userFolder ?? self::$userKey, $token, $lifetime, $site
        );
    }

    /**
     * Clear CSRF token
     *
     * @param string|null $token
     * @param string|null $userFolder
     * @param string|null $site
     * @return bool true if success
     */
    public function clearCsrfToken(
        ?string $token = null, 
        ?string $userFolder = null, 
        ?string $site = null): bool
    {
        $token = $this->getToken($token);
        return self::$csrfToken->clearCsrfToken(
            $userFolder ?? self::$userKey, $token, $site
        );
    }


    
}