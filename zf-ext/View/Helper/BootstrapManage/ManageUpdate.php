<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageUpdate
 * @copyright  Copyright (c)
 *             2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageUpdate.php 2014-18-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Update" on the grid view
 *
 * @uses      Zend_View_Helper_Abstract
 * @package   ZF_View_Helper
 * @subpackage ManageUpdate
 * @copyright Copyright
 *            2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageUpdate extends AbstractHelper
{
    private const DEFAULT_ICON = 'drive_file_rename_outline';

    /**
     * Create button "Update" element
     *
     * @param string $href     URL
     * @param string $title    Title
     * @param array  $attribs  Additional attributes
     *
     * @return string
     */
    public function __invoke(string $href, string $title = '', array $attribs = []): string
    {
        $title = empty($title) ? $this->view->translate('Cập nhật') : $title;

        $attribs['href'] = $href;
        $attribs['title'] = $title;
        $attribs['aclass'] = $attribs['aclass'] ?? '';

        return $this->view->manageIcon(self::DEFAULT_ICON, $attribs);
    }
}
