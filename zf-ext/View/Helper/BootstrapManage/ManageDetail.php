<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageDetail
 * @copyright  Copyright (c) 2005-2011 Zend Technologies
 * @version    $Id: ManageDetail.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zf\Ext\View\Helper\BootstrapManage;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Helper\AbstractHelper;

/**
 * Create a button "Detail" on the grid view
 *
 * @uses      AbstractHelper
 * @package   ZF_View_Helper
 * @subpackage ManageDetail
 */
class ManageDetail extends AbstractHelper
{
    /**
     * Create button "Detail" element
     *
     * @param string        $href
     * @param string|null   $title
     * @param array|null    $attribs
     *
     * @return string
     */
    public function __invoke(string $href, ?string $title = null, ?array $attribs = null): string
    {
        // Use null coalescing operator to get title if it's not set.
        $title = $title ?? $this->view->translate('Xem chi tiết');
        
        // Merge given attributes with the default ones.
        $attribs = array_merge([
            'href' => $href,
            'title' => $title,
            'aclass' => $attribs['aclass'] ?? '',
            'iclass' => 'text-info ' . ($attribs['iclass'] ?? ''),
        ], $attribs ?? []);
        
        // Set the button icon.
        $icon = 'visibility';

        // Return the icon using the "manageIcon" view helper.
        return $this->view->manageIcon($icon, $attribs);
    }
}
