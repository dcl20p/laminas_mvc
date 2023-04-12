<?php
/**
 * ZfUrl class
 *
 * This class is a plugin for generating URLs using the zfUrl view helper.
 *
 * @package    Zf\Ext\Controller
 * @subpackage Plugin
 */

declare(strict_types=1);

namespace Zf\Ext\Controller;

use Psr\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Zf\Ext\View\Helper\Url as ZfUrlHelper;

class ZfUrl extends AbstractPlugin
{
    const SERVICE_ALIAS = 'zfUrl';

    /**
     * The zfUrl view helper instance.
     *
     * @var ZfUrlHelper
     */
    protected ZfUrlHelper $url;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->url = $container->get('ViewHelperManager')->get(self::SERVICE_ALIAS);
    }

    /**
     * Generates a URL using the zfUrl view helper.
     *
     * @param string $name   The name of the route.
     * @param array  $opts   Options to pass to the view helper.
     * @param array  $params Parameters to pass to the view helper.
     *
     * @return string The generated URL.
     */
    public function __invoke(string $name = '', array $opts = [], array $params = []): string
    {
        return $this->url->__invoke($name, $opts, $params);
    }
}
