<?php
namespace Zf\Ext\Resource;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\Resource\ControllerResource;
class ViewHelperResource extends AbstractHelper{
    
    /**
     * Translate a message
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @throws Exception\RuntimeException
     * @return string
     */
    public function __invoke()
    {
        return ControllerResource::getInstance();
    }
}
?>