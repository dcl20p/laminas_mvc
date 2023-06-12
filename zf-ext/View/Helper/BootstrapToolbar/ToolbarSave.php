<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarSave
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Router\Http\TreeRouteStack;
use Zf\Ext\View\Helper\BootstrapToolbar\ToolbarIcon;

/**
 * Create a button "Save" on the toolbar
 *
 * @package Zend_View_Helper
 * @subpackage ToolbarSave
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarSave extends AbstractHelper
{
    /**
     * Create a button 'Insert' on the toolbar
     *
     * @param string $href     Url of the button
     * @param string $label    Label of the button
     * @param array  $attribs  Additional attributes of the button
     */
    public function __invoke(?string $href = null, ?string $label = null, array $attribs = [])
    {
        // Use default URL if $href is not set.
        $href = $href ?? 'javascript:void(0);';

        // Use default label if $label is not set.
        $label = $label ?? $this->view->translate('Lưu');

        // Use default if onclick attribute not set
        $attribs['onclick'] = "document.querySelector('#adminForm').submit(); void(0);";

        // Add attributes.
        $attribs['href'] = $href;
        $attribs['title'] = $label;
		$attribs ['label'] = $label;

        // Set button icon.
        $icon = 'save';

        // Initialize ToolbarIcon.
        return (new ToolbarIcon())->__invoke($icon, $attribs);
    }
}
