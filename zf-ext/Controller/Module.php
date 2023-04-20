<?php
/**
 * @link      http://github.com/laminas/laminas-db for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Zf\Ext\Controller\ControllerFactory;
use Zf\Ext\Controller\EntityManager;
use Zf\Ext\Controller\ZfRedirect;
use Zf\Ext\Controller\ZfTranslator;
use Zf\Ext\Controller\ZfAuthentication;
use Laminas\ServiceManager\Factory\InvokableFactory;

class Module
{
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
                    ZfRedirect::class       => InvokableFactory::class,
                    ZfUrl::class            => ControllerFactory::class,
                    EntityManager::class    => ControllerFactory::class,
                    ZfTranslator::class     => ControllerFactory::class,
                    ZfAuthentication::class => ControllerFactory::class,
                    ZfCsrfToken::class      => ControllerFactory::class,
                ],
                'aliases' => [
                    'zfRedirect'                    => ZfRedirect::class,
                    'zfUrl'                         => ZfUrl::class,
                    EntityManager::SERVICE_ALIAS    => EntityManager::class,
                    ZfTranslator::SERVICE_ALIAS     => ZfTranslator::class,
                    ZfAuthentication::SERVICE_ALIAS => ZfAuthentication::class,
                    ZfCsrfToken::SERVICE_ALIAS      => ZfCsrfToken::class,
                ]
            ],
        ];
    }
}
