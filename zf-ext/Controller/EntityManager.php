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
use Interop\Container\ContainerInterface;
use Zf\Ext\SqlLogger;

/**
 * @todo allow specifying status code as a default, or as an option to methods
 */
class EntityManager extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getEntityManager';
    /**
     * List of entity manager
     * @var array
     */
    protected static $_managers = null;
    
    /**
     * Set redis cache for entityManager
     * @param array $configs
     * @param \Doctrine\ORM\Configuration $confObj
     */
    protected function customRedisCacheNamespace(\Doctrine\ORM\Configuration $confObj ){
        foreach (['HydrationCacheImpl', 'MetadataCacheImpl', 'QueryCacheImpl','ResultCacheImpl'] as $fnc){
            $driver = $confObj->{"get{$fnc}"}();
            if ($driver instanceof \Doctrine\Common\Cache\RedisCache){
                $driver->setNamespace($fnc);
            }
        }
    }
    
    /**
     * Customer setting
     * @param string $key
     * @return \Doctrine\ORM\EntityManager | null
     */
    protected function initDoctrine($key, $container = null){
        
        if ( !isset(self::$_managers[$key]) ){
            
            $container = $container ?? $this->getController()->getEvent()
            ->getApplication()->getServiceManager();
            
            $serviceKey = "doctrine.entitymanager.{$key}";
            if ( $container->has($serviceKey) ){
                $logConfigs = $container->get('config')['doctrine']['driver']['logs'] ?? false;
                self::$_managers[$key] = $container->get($serviceKey);
                    
                $this->customRedisCacheNamespace(
                    self::$_managers[$key]->getConfiguration()
                );
                
                if ( !empty($logConfigs) ){
                    self::$_managers[$key]->getConfiguration()
                    ->setSQLLogger(
                        new SqlLogger($logConfigs)
                    );
                }
                
                unset($logConfigs, $container);
            }else {
                throw new \Exception("Doctrine entitymanager with name {$key} could not be found.");
            }
        }
        
        return self::$_managers[$key];
    }
    
    /**
     * Get entityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function __invoke($connectionName = 'orm_default', ?ContainerInterface $container = null) {
        $connectionName = $connectionName ?? 'orm_default';
        return self::$_managers[$connectionName] ?? $this->initDoctrine(
            $connectionName, $container
        );
    }
}