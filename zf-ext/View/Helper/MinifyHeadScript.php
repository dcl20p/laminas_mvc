<?php
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\HeadScript;

class MinifyHeadScript extends HeadScript
{
    protected $_minifyLocation = 'min/';

    protected $_regKey = 'RC_View_Helper_MinifyHeadScript';

    public function __invoke(
        $mode = self::FILE, 
        $spec = null, 
        $placement = 'APPEND', 
        array $attrs = [], 
        $type = 'text/javascript'
    ) {
        return parent::__invoke($mode, $spec, $placement, $attrs, $type);
    }

    /**
     * @see \Zend_View_Helper_HeadScript->toString()
     * @param  string|int $indent
     * @return string
     * @throws \Laminas\View\Exception\ExceptionInterface
     */
    public function toString($indent = null) {
        // An array of Script Items to be rendered
        $items = [];
        // An array of Javascript Items
        $scripts = [];

        // Any indentation we should use.
        $indent = (null !== $indent) 
            ? $this->getWhitespace($indent) 
            : $this->getIndent();

        // Determining the appropriate way to handle inline scripts
        if ($this->view) {
			$useCdata = $this->view->doctype()->isXhtml() ? true : false;
		} else {
			$useCdata = $this->useCdata ? true : false;
		}

        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd = ($useCdata) ? '//]]>' : '//-->';

        $this->getContainer()->ksort();
        $minifyScripts = $this->getContainer()->getArrayCopy();
        $inlineScripts = $this->view->inlinescript ()->getContainer()->getArrayCopy();
        $allScripts = array_merge($minifyScripts, $inlineScripts);
        unset($minifyScripts, $inlineScripts);

        // -- loop
        foreach ($allScripts as $item) {
            if ($this->_isNeedToMinify($item)) {

                if (!empty($item->attributes['minify_split_before']) || !empty($item->attributes['minify_split'])) {
                    $items[] = $this->_generateMinifyItem($scripts);
                    $scripts = [];
                }                
                if (!empty($item->attributes['src'])) {
                    $scripts[] = $item->attributes['src'];
                }                
                if (!empty($item->attributes['minify_split_after']) || !empty($item->attributes['minify_split'])) {
                    $items[] = $this->_generateMinifyItem($scripts);
                    $scripts = [];
                }
            } else {
                if ($scripts) {
                    $items[] = $this->_generateMinifyItem($scripts);
                    $scripts = [];
                }
                $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
            }
        }
        if ($scripts) {
            $items[] = $this->_generateMinifyItem($scripts);
        }        
        
        return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }

    protected function _isNeedToMinify($item)
    {
        $isMinifiable = isset($item->attributes['src']) 
            && !empty($item->attributes['src']) 
            && preg_match('/^https?:\/\//', $item->attributes['src']) == false;
        $isDisabled = isset($item->attributes['minify_disabled']);
        return $isMinifiable && !$isDisabled;
    }

    protected function _generateMinifyItem(array $scripts)
    {
        $baseUrl = $this->getBaseUrl();
        if (substr($baseUrl, 0, 1) == '/') {
            $baseUrl = substr($baseUrl, 1);
        }        
        $minScript = new \stdClass();
        $minScript->type = 'text/javascript';
        if (is_null($baseUrl) || $baseUrl == '') {
            $minScript->attributes['src'] = $this->getMinUrl() . '?f=' . implode(',', $scripts);
        } else {
            $minScript->attributes['src'] = $this->getMinUrl() . '?b=' . $baseUrl . '&f=' . implode(',', $scripts);
        }
        return $this->itemToString($minScript, '', '', '');
    }

    /**
     * Retrieve the minify url
     * @return string
     */
    public function getMinUrl() {
        return $this->getBaseUrl() . $this->_minifyLocation;
    }

    /**
     * Retrieve the currently set base URL
     *
     * @return string
     */
    public function getBaseUrl() {
        return '/';
    }
}