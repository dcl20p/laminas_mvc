<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageChangeStatus
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageChangeStatus.php 2014-19-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Status" on the grid view
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ManageChangeStatus
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageChangeStatus extends AbstractHelper {
	/**
	 * Create a button "Status" element
	 * 
	 * @param string $href
	 * @param string $status
	 * @param array $attribs
	 *        	
	 * @return string
	 */
	public function __invoke(string $href, string $status, array $attribs = []): string 
	{
		$icon = $status ? 'fa-check' : 'fa-circle';

		$title = match ($status) {
            true => $this->view->translate('Bỏ kích hoạt'),
            default => $this->view->translate('Kích hoạt'),
        };
		
		$attribs = [
			'href' => $href,
			'title' => $title,
			'aclass' => 'change-status ' . ($attribs['aclass'] ?? ''),
		];

		// Initialize manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}