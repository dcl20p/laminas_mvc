<?php 
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapManage
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\EscapeHtmlAttr;

/**
 * Create a "Checkbox" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 */
class ManageCheckbox extends AbstractHelper
{
    private string $_formatIcon = '<div class="form-check my-auto">
        <input class="form-check-input" type="checkbox" name="id[]" value="%s" %s />
    </div>';

    /**
     * Create a checkbox element
     *
     * @param mixed $value The value of the checkbox
     * @param array $attribs The attributes of the checkbox
     * @return string The checkbox element
     */
    public function __invoke(mixed $value, array $attribs = []): string
    {
        $attributes = '';
        foreach ($attribs as $key => $val) {
            $attributes .= sprintf(
                ' %s="%s"', 
                $this->view->escapeHtmlAttr($key), 
                $this->view->escapeHtmlAttr($val)
            );
        }

        return sprintf($this->_formatIcon, $value, $attributes);
    }
}
