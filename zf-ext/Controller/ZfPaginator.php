<?php
namespace Zf\Ext\Controller;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use Zf\Ext\Model\ZFDtPaginator;

class ZfPaginator extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getPaginator';

    public function __construct(ContainerInterface $container)
    {}

    public function __invoke($query)
    {
        return new ZFDtPaginator($query);
    }  
}