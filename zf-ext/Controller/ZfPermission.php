<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use Zf\Ext\Router\RouterLiteral;

/**
 * Provides access control list (ACL) support for controllers.
 *
 * @todo Allow specifying status code as a default, or as an option to methods.
 */
class ZfPermission extends AbstractPlugin
{
    public const SERVICE_ALIAS = 'getPermission';
    public const ZF_DEFAULT_ROLE = 'all';
    public const ZF_DEFAULT_CODE = 'STAFF';
    public const ZF_ACL_FOLDER = 'zf_acl';

    /**
     * The Zend authentication object.
     *
     * @var \Laminas\Permissions\Acl\Acl|null
     */
    protected $_pACL = null;

    /**
     * An array of configuration options for the plugin.
     *
     * @var array
     */
    protected array $_pConfigs = [];

    /**
     * The subfolder to use.
     *
     * @var string
     */
    protected string $_subFolder = '';

    /**
     * The key to use for user authentication.
     *
     * @var string
     */
    protected string $_userKey = 'admin_groupcode';

    /**
     * ZfPermission constructor.
     *
     * @param ContainerInterface $container The service container.
     */
    public function __construct(ContainerInterface $container)
    {
        $configs = $container->get('config');
        $pConfigs = $configs['zf_permission'][APPLICATION_SITE] ?? [];

        if (!empty($pConfigs) && !empty($configs['zf_permission'][APPLICATION_SITE])) {
            $this->_pACL = true;

            $this->_userKey = $configs['zf_permission'][APPLICATION_SITE]['user_key'] ?? $this->_userKey;

            if (($configs['zf_permission'][APPLICATION_SITE]['use_subfolder'] ?? false)) {
                $this->_subFolder = DIRECTORY_SEPARATOR . APPLICATION_SITE;
            }

            $routes = $configs['router'] ?? [];
            $prevents = $configs['zf_permission'][APPLICATION_SITE]['prevent_routes'] ?? [];

            $this->_pConfigs = [
                'routes' => $routes['routes'] ?? [],
                'prevents' => $prevents,
            ];

            unset($prevents, $routes);
        }
    }

    /**
     * Determines whether this instance is unregistered.
     *
     * @return bool Returns true if the ACL is not registered, false otherwise.
     */
    public function isUnRegistered(): bool
    {
        return empty($this->_pACL);
    }

    /**
     * Returns the configuration options for this plugin.
     *
     * @return array The configuration options.
     */
    public function getConfigs(): array
    {
        return $this->_pConfigs;
    }

    /**
     * Transforms an action token into a method name.
     *
     * @param string $action The action token to transform.
     * @return string The transformed method name.
     */
    protected static function getMethodFromAction(string $action): string
    {
        $method = str_replace(['.', '-', '_'], ' ', $action);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        $method = lcfirst($method);
        $method .= 'Action';

        return $method;
    }

    /**
     * Convert string to action name
     *
     * @param string $str The input string to convert
     * @return string The resulting action name
     */
    protected function makeActName($str = '')
    {
        $names = explode('-', $str);
        return array_reduce($names, function ($result, $item) {
            return $result . ucfirst($item);
        }, array_shift($names)) . 'Action';
    }

    /**
     * Get URL by route name
     *
     * @param string|null $name The name of the route
     * @param array $params The parameters to include in the URL
     * @param bool $debug Whether to output debug information
     * @return string The resulting URL
     */
    protected function getZfUrlByRoute(?string $name, array $params = [], bool $debug = false): string
    {
        if (null === $name || empty($this->_pConfigs['routes'][$name])) {
            return '';
        }
        $route = $this->_pConfigs['routes'][$name];
        if (RouterLiteral::class === $route['type']) {
            $controller = $route['options']['defaults']['controller'] ?? '';
            $actName = $this->getMethodFromAction($route['options']['defaults']['action'] ?? 'index');
        } else {
            $controller = $route['options']['defaults']['controller'] ?? '';
            $actName = $this->getMethodFromAction($params['action'] ?? 'index');
        }
        return implode('\\', [$controller, $actName]);
    }

    /**
     * @param string|null $routeName
     * @param array $params
     * @param bool $debug
     * @return bool
     */
    public function checkPermission(?string $routeName = '', array $params = [], bool $debug = false): bool
    {
        if ($this->isUnRegistered()) {
            return true;
        }
        $controller = $this->getController();
        $authen = new \stdClass();
        if (!empty($controller)) {
            $authen = $controller->getAuthen();
            $routeName = $routeName ?? $controller->getCurrentRouteName();
        }
        if (!empty($this->_pConfigs['prevents'][$routeName])) {
            $rolePrevent = $this->_pConfigs['prevents'][$routeName];
            if (is_bool($rolePrevent)) {
                return $rolePrevent;
            }
            if (is_callable($rolePrevent)) {
                return call_user_func($rolePrevent, $authen);
            }
            return boolval($rolePrevent);
        }
        $role = ($authen->{$this->_userKey} ?? self::ZF_DEFAULT_CODE);
        if ($role === 'SUPPORT') {
            return true;
        }
        $uri = $this->getZfUrlByRoute($routeName, $params, $debug);
        $role = crc32((string)$role);
        $opts = explode('\\', $uri);
        $filePath = implode(DIRECTORY_SEPARATOR, [
            DATA_PATH, self::ZF_ACL_FOLDER . $this->_subFolder, crc32(array_shift($opts)),
            crc32($routeName . '\\' . end($opts)) . '.php'
        ]);
        
        // -- File not exists, file empty or isset => Granted
        if ( realpath($filePath) 
            && false == empty($perms = @include $filePath)
            && isset($perms[$role]) ) return true;
        
        return false;
    }
}