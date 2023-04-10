<?php

/**
 * @link      http://github.com/laminas/laminas-router for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Router;

use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\RouteMatch;

/**
 * Literal route.
 */
class RouterLiteral extends Literal
{
    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::match()
     * @param  Request      $request
     * @param  integer|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if (BASE_URL !== '/' && $path !== '/') {
            $pattern = '/^(' . preg_quote(BASE_URL, '/') . '\/){1}/';
            $count = 0;
            $path = rtrim(preg_replace($pattern, '/', $path, -1, $count), '/');
            $path = $count === 0 && (empty($path) || BASE_URL === $path) ? '/' : $path;
            $request->setUri($path);

            if (method_exists($request, 'setBaseUrl')) {
                $request->setBaseUrl(BASE_URL);
            }

            if (method_exists($request, 'setRequestUri')) {
                $request->setRequestUri($path);
            }
        }

        if ($pathOffset !== null && $pathOffset >= 0 && strlen($path) >= $pathOffset && !empty($this->route)) {
            if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
                $request->setUri('/project/index');

                if (method_exists($request, 'setBaseUrl')) {
                    $request->setBaseUrl($this->route);
                }

                if (method_exists($request, 'setRequestUri')) {
                    $request->setRequestUri('/project/index');
                }

                return new RouteMatch($this->defaults, strlen('/project/index'));
            }

            return null;
        }

        if ($path === $this->route) {
            return new RouteMatch($this->defaults, strlen($this->route));
        }

        return null;
    }
}
