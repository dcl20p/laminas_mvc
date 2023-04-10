<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfController extends AbstractActionController
{
    /**
     * Default message on error occurred
     */
    const DEFAULT_ERROR_MSG = 'Some error occurred';

    /**
     * @param Get match route name
     * @return string
     */
    public function getCurrentRouteName(){
        return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
    }
}
