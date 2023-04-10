<?php
/**
 * @link      http://github.com/laminas/laminas-db for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Resource;

// use Zf\Ext\Resource\ViewHelperResource;
use Zf\Ext\Resource\ControllerResource;
use Zf\Ext\Resource\ResourceFactory;

class Module {
    /**
     * Retrieve default zend-db configuration for zend-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'controller_plugins' => [
                'factories' => [
                    // ControllerResource::class => ResourceFactory::class,
                ],
                'aliases' => [
                    // 'zfResource' => ControllerResource::class,
                ]
            ],
            'view_helpers' => [
                'factories' => [
                    ViewHelperResource::class => ResourceFactory::class
                ],
                'aliases' => [
                    'zfResource' => ViewHelperResource::class,
                ]
            ]
        ];
    }
}