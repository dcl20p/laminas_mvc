<?php

namespace Zf\Ext\Resource;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf\Ext\Resource\ControllerResource;

/**
 * The factory responsible for creating of Resource service.
 */
class ResourceFactory implements FactoryInterface
{
    /**
     * This method creates the Laminas\Authentication\AuthenticationService service 
     * and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $appResource = isset($config['app_resource']) ? $config['app_resource'] : '';
        
        // Create the service and inject dependencies into its constructor.
        return $requestedName === ControllerResource::class
            ? ControllerResource::getInstance($appResource)
            : new $requestedName(ControllerResource::getInstance($appResource));
    }

    /**
     *
     * @param ServiceLocatorInterface $container
     * @return LoaderPluginManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, 'zfResource');
    }
}