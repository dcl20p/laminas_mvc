<?php
/**
 * Create a "Delete" button for the toolbar.
 *
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarClose
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;

use Laminas\Http\PhpEnvironment\Request;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapToolbar\ToolbarIcon;

/**
 * Create a "Delete" button for the toolbar.
 */
class ToolbarClose extends AbstractHelper
{
    /**
     * @var Laminas\Router\Http\TreeRouteStack
     */
    private $_matchRouter = null;

    public function __construct(Request $request, TreeRouteStack $router)
    {
        $this->_matchRouter = $router->match($request);
    }

    /**
     * Create a "Close" button for the toolbar.
     *
     * @param string|null $href URL
     * @param string|null $label Label
     * @param array $attribs
     * @return string
     */
    public function __invoke(?string $href = null, ?string $label = null, array $attribs = []): string
    {
        // Use href from current route if it is not given
        $href = $href ?? $this->view->zfUrl(
			$this->_matchRouter->getMatchedRouteName(), 
            ['action' => 'index'], ['useOldQuery' => true]
		);

        // Use label from translation if it is not given
        $label = $label ?? $this->view->translate('Đóng');

        // Add href and label to attribs array
        $attribs['href'] = $href;
        $attribs['label'] = $label;
        $attribs['title'] = $label;

        // Set button icon.
        $icon = 'cancel';

        // Initialize ToolbarIcon.
        return (new ToolbarIcon())->__invoke($icon, $attribs);
    }
}
