<?php
/**
 * Custom Redirect plugin for ZfExt module.
 *
 * @package Zf\Ext\Controller
 */

namespace Zf\Ext\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Exception;
use Laminas\Mvc\InjectApplicationEventInterface;
use \Laminas\Mvc\Controller\Plugin\Redirect;

class ZfRedirect extends Redirect
{
    /**
     * @var \Laminas\Mvc\MvcEvent
     */
    protected $event;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Redirect to a specific route.
     *
     * @param string|null $route Name of the route to redirect to.
     * @param array $params Parameters to use in the URL generation.
     * @param array $options Options to use in the URL generation.
     * @param bool $reuseMatchedParams Whether to reuse matched parameters.
     *
     * @return Response Returns the HTTP response.
     *
     * @throws Exception\DomainException If the controller does not define the plugin() method.
     */
    public function toRoute($route = null, $params = [], $options = [], $reuseMatchedParams = false): Response
    {
        $controller = $this->getController();
        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Redirect plugin requires a controller that defines the plugin() method');
        }

        $urlPlugin = $controller->plugin('url');

        $options['useOldQuery'] ??= false;
        if ($options['useOldQuery']) {
            $oldQuery = $this->getOldQuery(['router' => $route, 'action' => $params['action'] ?? '']);
            if ($oldQuery) {
                $newQuery = array_replace($oldQuery, (array) ($options['query'] ?? []));
                $options['query'] = $newQuery;
            }
            unset($options['userOldQuery']);
        }

        $url = match (true) {
            is_scalar($options) => $urlPlugin->fromRoute($route, $params, $options),
            default => $urlPlugin->fromRoute($route, $params, $options, $reuseMatchedParams),
        };

        $base = rtrim(BASE_URL, '/');
        $baseNew = ltrim($url, '/');
        $options['force_canonical'] ??= false;

        if ($options['force_canonical']) {
            $url1 = $urlPlugin->fromRoute($route, $params);
            $url = str_replace($url1, $base . $url1, $baseNew);
        } else {
            $url = implode('/', [$base, $baseNew]);
        }

        return $this->toUrl($url);
    }

    /**
     * Redirect to the current route.
     *
     * @param array $params Parameters to use in the URL generation.
     * @param array $options Options to use in the URL generation.
     * @param bool $reuseMatchedParams Whether to reuse matched parameters.
     *
     * @return Response Returns the HTTP response.
     */
    public function toCurrentRoute($params = [], $options = [], $reuseMatchedParams = false): Response
    {
        return $this->toRoute(
            $this->getController()->getCurrentRouteName(),
            $params, $options, $reuseMatchedParams
        );
    }

    /**
     * Get the old query parameters based on the current route and action.
     *
     * @param array $params Additional parameters to use in the query string lookup.
     *
     * @return array Returns the old query parameters.
     */
    protected function getOldQuery(array $params = []): array
    {
        $action = $params['action'] ?? 'index';

        $key = crc32(json_encode([$params['router'], $action]));
        $oldQuery = [];

        $container = new \Laminas\Session\Container("queryStringMn");
        $ssManager = $this->getEvent()->getApplication()->getServiceManager();

        if ($ssManager->has('Laminas\Session\SessionManager')) {
            $container::setDefaultManager($ssManager->get('Laminas\Session\SessionManager'));
        }

        if ($container->offsetExists('queryString')) {
            $oldQuery = $container->offsetGet('queryString');
            $container->offsetUnset('queryString');
        }

        unset($container);

        return $oldQuery;
    }
}
