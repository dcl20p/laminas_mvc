<?php 
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapManage
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;

/**
 * Create a "Checkbox" on the gridview, use check all checkbox has attribute name="id[]"
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 */
class ManageCheckboxAll extends AbstractHelper
{
    private string $_formatIcon = '<div class="form-check my-auto">
        <input class="form-check-input" type="checkbox" id="checkall" %s />
    </div>';

    /**
     * Create a checkbox element
     *
     * @param array $attribs The attributes of the checkbox
     * @return string The checkbox element
     */
    public function __invoke(array $attribs = []): string
    {
        $attributes = '';
        foreach ($attribs as $key => $val) {
            $attributes .= sprintf(
                ' %s="%s"', 
                $this->view->escapeHtmlAttr($key), 
                $this->view->escapeHtmlAttr($val)
            );
        }

        return sprintf($this->_formatIcon, $attributes);
    }
}
