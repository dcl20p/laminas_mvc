<?php
namespace Zf\Ext\Controller;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory responsible for creating of Resource service.
 */
class ControllerFactory implements FactoryInterface
{
    public $_serviceName = null;
    
    /**
     * This method creates the Laminas\Authentication\AuthenticationService service 
     * and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->_serviceName = $requestedName::SERVICE_ALIAS;
        // Create the service and inject dependencies into its constructor.
        return new $requestedName($container);
    }
}