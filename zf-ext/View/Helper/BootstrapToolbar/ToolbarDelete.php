<?php
/**
 * Create a "Delete" button for the toolbar.
 *
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarDelete
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
class ToolbarDelete extends AbstractHelper
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
     * Create a "Delete" button for the toolbar.
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
			$this->_matchRouter->getMatchedRouteName(), ['action' => 'delete']
		);

        // Use label from translation if it is not given
        $label = $label ?? $this->view->translate('Xóa');

        // Add href and label to attribs array
        $attribs['href'] = $href;
        $attribs['label'] = $label;
        $attribs['title'] = $label;

        // Add CSS class to attribs array
        $attribs['aclass'] = ($attribs['aclass'] ?? '') . ' toolbar-delete';

        // Add data attributes to attribs array
        $attribs['data-confirm'] = $attribs['data-confirm'] ?? $this->view->translate(
			'Bạn có chắc muốn xóa những dòng đã chọn?'
		);
        $attribs['data-rq-one'] = $this->view->translate('Vui lòng chọn ít nhất 1 dòng');

        // Set icon class
        $icon = 'do_not_disturb_on';

        // Create the button HTML
        return (new ToolbarIcon())->__invoke($icon, $attribs);
    }
}
