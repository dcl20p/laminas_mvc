<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarInsert
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
 * Create a button "Insert" on the toolbar
 *
 * @package Zend_View_Helper
 * @subpackage ToolbarInsert
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarInsert extends AbstractHelper
{
    /**
     * @var Laminas\Router\Http\TreeRouteStack
     */
    private $_matchRouter = null;

    public function __construct(Request $request, TreeRouteStack $router)
    {
        $this->_matchRouter = $router;
    }

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
        if (!isset($href)) {
            $href = $this->view->zfUrl(
                $this->_matchRouter->getMatchedRouteName(),
                ['action' => 'add']
            );
        }

        // Use default label if $label is not set.
        $label = $label ?? $this->view->translate('Thêm');

        // Add attributes.
        $attribs['href'] = $href;
        $attribs['title'] = $label;
		$attribs ['label'] = $label;

        // Set button icon.
        $icon = 'add_circle';

        // Initialize ToolbarIcon.
        return (new ToolbarIcon())->__invoke($icon, $attribs);
    }
}
