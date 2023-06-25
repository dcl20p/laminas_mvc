<?php 
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageDelete
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc.
 * @version    $Id: ManageDelete.php 2014-18-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Delete" on the grid view
 *
 * @uses      AbstractHelper
 * @package   ZF_View_Helper
 * @subpackage ManageDelete
 */
class ManageDelete extends AbstractHelper {
	/**
	 * Create button "Delete" element
	 * 
	 * @param string $href Url
     * @param string|null $title Title
     * @param array<string,mixed> $attribs
     * @return string
	 */
	public function __invoke(string $href, ?string $title = null, array $attribs = []): string
	{
		// If no title provided.
		$title = $title ?? $this->view->translate ("Xóa");
		
		// Add to attribs array.
		$attribs['aclass'] = 'manage-delete ' . ($attribs['aclass'] ?? '');
		$attribs['iclass'] = 'text-danger ' . ($attribs['iclass'] ?? '');
		
		$attribs = array_merge([
			"href"  => 'javascript:void(0);',
			"title" => $title,
			"data-confirm" => $attribs ["data-confirm"] ?? $this->view->translate('Bạn có chắc muốn xóa dòng này?'),
			"data-href" => $href,
		], $attribs ?? []);
		// Button icon.
		$icon = 'delete';
		
		// Initialize manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}
