<?php
namespace Zf\Ext\View\Helper;

/**
 * Helper class for generating links and retrieving URLs based on routes and routers.
 *
 * @uses Laminas\View\Helper\AbstractHelper
 * @package ZF_View
 * @subpackage Helper
 */
class HeadScriptAction extends BaseHeadAction 
{
    /**
     * Constant for file format.
     *
     * @var string
     */
    const _suffixFile = '.js';

    /**
     * Constant for folder name that contains the files.
     *
     * @var string
     */
    const _folderSrc = 'js';

    /**
     * Constant for view folder name.
     *
     * @var string
     */
    const _folderView = 'view';

    /**
     * Constant for assets folder name.
     *
     * @var string
     */
    const _folderAssets = 'assets';

    /**
     * Retrieves file content in .js format.
     *
     * @param string|Zend_Controller_Request_Abstract $request
     * @param array $params
     * @param array $options
     * @param bool $isPrepend
     * @param string|null $type
     * @return mixed
     */
    public function __invoke($request, $params = [], $options = [], $isPrepend = true, $type = null)
    {
        $fileContent = $this->getFileContent($request, $params, $options);
        
        // Get resource content only
        if (true === ($options['getContent'] ?? false)) {
            return $fileContent;
        }
        
        // Prepend to layout
        if (true === $isPrepend) {
            $this->view->inlinescript()->prependScript(
                $fileContent,
                $type,
                ['getContent' => true]
            );
        } else { // Append to layout
            $this->view->inlinescript()->appendScript(
                $fileContent,
                $type,
                ['getContent' => true]
            );
        }
    }
}
