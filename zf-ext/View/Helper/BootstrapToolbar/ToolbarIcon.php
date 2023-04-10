<?php
/**
 * Creates a button on the Bootstrap Toolbar
 *
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;

use Laminas\View\Helper\AbstractHelper;

class ToolbarIcon extends AbstractHelper
{
    /**
     * HTML format string to render the button.
     *
     * @var string
     */
	private $_formatIcon = '<li class="me-4">
		<a class="d-flex fs-7 align-items-center %s" href="%s" title="%s" onclick="%s" target="%s" %s>
			<i class="material-icons fs-6 me-1">%s</i>
			<span>%s</span>
		</a>
	</li>';

    /**
     * Creates a button on the Bootstrap Toolbar.
     *
     * @param string $icon Icon for the button
     * @param array $attribs Attributes array:
     *        'aclass', 'href', 'onclick', 'title', 'target', 'sclass', and 'label'.
     * @return string The button XHTML.
     */
    public function __invoke(string $icon, array $attribs = [])
    {
        if (isset($attribs['href']) && $attribs['href'] === '#') {
            return '';
        }

        $attributes = '';
		$attribs = array_map('htmlspecialchars', $attribs);
        foreach ($attribs as $key => $val) {
            if (!in_array($key, ['aclass', 'href', 'title', 'onclick', 'target', 'sclass', 'label'])) {
                $attributes .= htmlspecialchars($key) . '="' . $val . '" ';
            }
        }

        return sprintf(
            $this->_formatIcon,
            $attribs['aclass'] ?? '',
            $attribs['href'] ?? '',
            $attribs['title'] ?? '',
            $attribs['onclick'] ?? '',
            $attribs['target'] ?? '',
            $attributes,
            $icon,
            $attribs['label'] ?? ''
        );
    }

    /**
     * Returns the button XHTML.
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }
}
