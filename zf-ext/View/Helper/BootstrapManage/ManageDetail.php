<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageDetail
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc.
 * @version    $Id: ManageDetail.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\I18n\Translator\TranslatorInterface;

use Laminas\View\Helper\AbstractHelper;

/**
 * Create a button "Detail" on the gridview
 *
 * @uses      AbstractHelper
 * @package   ZF_View_Helper
 * @subpackage ManageDetail
 */
class ManageDetail extends AbstractHelper
{
    /**
     * Create button "Detail" on the gridview
     * 
     * @param string                   $href
     * @param string|null              $title
     * @param array<string,mixed>|null $attribs
     */
    public function __invoke(string $href, ?string $title = null, ?array $attribs = null): string
    {
        // TH: không có title.
        $title = $title ?? $this->getTranslator()->translate('Xem chi tiết');
        
        // Them vao mang attribs.
        $attribs = array_merge($attribs ?? [], [
            'href' => $href,
            'title' => $title,
            'aclass' => $attribs['aclass'] ?? '',
        ]);
        
        // Icon cua button.
        $icon = 'visibility';
		
        // Khoi tao manageIcon.
        return $this->view->manageIcon($icon, $attribs);
    }

    /**
     * Get translator instance.
     *
     * @return TranslatorInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->view->plugin('translate')->getTranslator();
    }
}
