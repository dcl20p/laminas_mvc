<?php
/**
 * @category   Zf
 * @package    Zf\Ext\View\Helper\BootstrapManage
 * @copyright  Copyright (c)
 * @version    $Id: ManageIcon.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;

/**
 * Create a "Button" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageIcon extends AbstractHelper
{
    private string $_formatIcon = '<a data-bs-toggle="tooltip" 
		class=" %s" href="%s" data-bs-original-title="%s" 
		onclick="%s" target="%s" %s>
		<i class="material-icons text-secondary position-relative text-lg">%s</i>
	</a>';

    public function __invoke(string $icon, array $attribs = []): string
    {
        // Không render button.
        if ($attribs["href"] === '#') {
            return '';
        }

        // Chuỗi các thuộc tính của button.
        $attributes = '';
        foreach ($attribs as $key => $val) {
            if (in_array($key, ['aclass', 'href', 'title', 'onclick', 'target', 'iclass'])) {
                $val = htmlspecialchars($val);
                $attribs[$key] = $val;
            } elseif (trim($val) !== '') {
                $attributes .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($val));
            }
        }

        // Không có href.
        if (trim($attribs['href']) === '') {
            $attribs['href'] = 'javascript:void(0);';
        }

        // Render button.
        return sprintf(
            $this->_formatIcon,
            $attribs['aclass'] ?? '',
            $attribs['href'] ?? '',
            htmlspecialchars($attribs['title'] ?? ''),
            $attribs['onclick'] ?? '',
            $attribs['target'] ?? '',
            $attributes,
            htmlspecialchars($icon)
        );
    }

    public function __toString(): string
    {
        return $this->__invoke('', []);
    }
}
