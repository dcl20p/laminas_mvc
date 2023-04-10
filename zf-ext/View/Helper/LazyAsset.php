<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: BootstrapToolbar.php 2014.20.01
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Generates a "button" element into the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class LazyAsset extends AbstractHelper {
    protected static $_assets = null;

    public function __construct() {
       if (!self::$_assets) { 
           self::$_assets = [
               'css' => [],
               'js'  => []
           ];
       }
    }

    /**
     * @return $this
     */
    public function __invoke()  {
        return $this;
    }

    public function addItems(array $assets) {
        $rs = [];
        foreach (self::$_assets as $key => $asset) {
            $rs[$key] = array_replace($asset, (array)($assets[$key] ?? []));
        }
        self::$_assets = $rs;
        
        unset($rs);
        return $this;
    }
    
    public function getItems() {
        return self::$_assets;
    }
}