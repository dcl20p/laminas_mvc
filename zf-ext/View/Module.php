<?php
namespace Zf\Ext;

class Module
{
    /**
     * Get autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
        ];
    }
}