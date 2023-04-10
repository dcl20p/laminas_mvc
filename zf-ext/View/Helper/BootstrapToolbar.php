<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: BootstrapToolbar.php 2014-20-01
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
class BootstrapToolbar extends AbstractHelper {

    private $_toolbarIcons = [];

    /**
     * Tao thanh toolbar.
     *
     * @param array $toolbarIcons
     *        	Button cua thanh toolbar
     * @return this
     */
    public function __invoke(array $toolbarIcons = []) 
    {
        foreach ($toolbarIcons as $toolbarIcon) {
            $this->_toolbarIcons[] = $toolbarIcon;
        }
        return $this;
    }

    public function __toString() 
    {
        $toolbarIconsHtml = implode('', $this->_toolbarIcons);
        return '<div class="collapse navbar-collapse justify-content-end nav-expand-toolbar" id="navToolbar">
            <ul class="navbar-nav text-white">' . $toolbarIconsHtml . '</ul>
        </div>';
    }

    public function toString() 
    {
		return $this->__toString();
	}
}
