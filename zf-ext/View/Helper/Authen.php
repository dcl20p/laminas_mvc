<?php
namespace Zf\Ext\View\Helper;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Helper\AbstractHelper;
use Psr\Container\ContainerInterface;

class Authen extends AbstractHelper
{
    protected $_authen = null;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if ($container->has(AuthenticationService::class)) {
            $this->_authen = $container->get(AuthenticationService::class)->getIdentity();
        }
    }

    /**
     *
     * @return void
     */
    public function __invoke()
    {
        return $this->_authen;
    }
}