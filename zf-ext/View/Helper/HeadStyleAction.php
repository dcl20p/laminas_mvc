<?php
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for making easy links and getting urls that depend on the routes and routers
 *
 * @uses Laminas\View\Helper\AbstractHelper
 * @package ZF_View
 * @subpackage Helper
 */
class HeadStyleAction extends BaseHeadAction 
{
	/**
     * Constant for file format.
     *
     * @var string
     */
	const _suffixFile = '.css';

	/**
	 * Constant for folder name that contains the files.
	 * 
	 * @var string
	 */
	const _folderSrc = 'css';
	
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
	
	protected static $cssMiner = null;
	/**
	 * Retrieves file content in .css format.
	 * 
	 * @param string|Zend_Controller_Request_Abstract $request 	
	 * @param array $params        	
	 * @param array $options        	
	 * @return mixed
	 */
	public function __invoke($request, $params = [], $options = []) 
	{
	    $fileContent = $this->getFileContent($request, $params, $options);
	    
	    // Get resource content only
	    if ( true === ($options ['getContent'] ?? false)) {
	        return $fileContent;
	    }
		
		// Append to layout
		$this->view->headStyle()->appendStyle ( 
		    $fileContent,
		    ['media' => 'all'], 
		    ['getContent' => true]
		);
	}
}
?>