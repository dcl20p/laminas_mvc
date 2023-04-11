<?php
namespace Zf\Ext\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

/**
 * Helper for creating and retrieving URLs that depend on the routes and router
 */
class BaseHeadAction extends AbstractHelper 
{
    /**
     * @var array The request parameters
     */
    protected array $rqParams = [];

    /**
     * Constructor
     * 
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        if ($container->has('router') && $container->has('request')) {
            $this->rqParams = $container->get('router')->match(
                $container->get('request')
            )->getParams();

            $this->parseCtrlModule($this->rqParams['controller'] ?? '');
        }
    }

    /**
     * Parses the controller and module names from the given string
     * 
     * @param string $str The string to parse
     */
    protected function parseCtrlModule(string $str = ''): void
    {
        $str = explode('\\', $str);

        $this->rqParams['module'] = array_shift($str) ?? '';
        $this->rqParams['controller'] = str_replace('Controller', '', array_pop($str) ?? '');

        foreach ($this->rqParams as $key => $val) {
            $this->rqParams[$key] = $this->convertCamelToSnake($val);
        }
    }

    /**
     * Converts a camel case string to snake case
     * 
     * @param string $str The string to convert
     * 
     * @return string The converted string
     */
    protected function convertCamelToSnake(string $str = ''): string
    {
        $str = lcfirst($str);

        return strtolower(preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', 
            '-', 
            $str
        ));
    }

    /**
     * Returns the value of the specified request parameter
     * 
     * @param string $key The name of the request parameter
     * @param array $options The options to use when retrieving the value
     * 
     * @return mixed|null The value of the request parameter, or null if it doesn't exist
     */
    protected function getRqParams(string $key, array $options = [])
    {
        return $options[$key] ?? $this->rqParams[$key] ?? null;
    }

    /**
     * Gets the content of the specified file
     * 
     * @param string|null $content The file content (if already loaded)
     * @param array $params The parameters to bind to the file content
     * @param array $options The options to use when getting the file content
     *  - action: The name of the action
     *  - controller: The name of the controller
     *  - module: The name of the module
     * 
     * @return string The content of the file
     */
    public function getFileContent(?string $content = null, array $params = [], array $options = []): string 
    {
        $fileContent = '';

        if (is_string($content)) {
            $fileContent = $content;
        } else {
            $moduleName = $this->getRqParams('module', $options) ?? 'application';
            $controllerName = $this->getRqParams('controller', $options) ?? 'index';
            $actionName = $this->getRqParams('action', $options) ?? 'index';
            $callerClss = get_called_class();

            $site = defined('APPLICATION_SITE') ? APPLICATION_SITE : '';

            // Get file path
            $base = str_replace(' ', '', ucwords(str_replace('-', ' ', $moduleName)));
            $arrPath = array_filter([
                APPLICATION_PATH, $site, $base,
                $callerClss::_folderView, $callerClss::_folderAssets,
                $controllerName, $callerClss::_folderSrc, $actionName
            ]);

            $filePath = realpath(implode('/', $arrPath) . $callerClss::_suffixFile);

            // Get file content
            if ($filePath) {
                $fileContent = file_get_contents($filePath);
            }
        }
        // Bind data
        if ( !empty($params) ) {
            $fileContent = str_replace ( 
                array_keys ($params), 
                array_values ($params), 
                $fileContent
            );
        }

        return $fileContent;
    }
}
?>