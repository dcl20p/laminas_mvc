<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: BootstrapToolbar.php 2014-20-01
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zf\Ext\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\Utilities\CsrfToken as ZfToken;

/**
 * Generates a "button" element into the toolbar
 *
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 */
class CsrfToken extends AbstractHelper
{
    const SERVICE_ALIAS = 'zfCsrfToken';

    /**
     * @var ZfToken|null
     */
    protected static ?ZfToken $csrfToken = null;

    /**
     * @var string
     */
    protected string $_userFolder = '';

    public function __construct(ContainerInterface $container)
    {
        self::$csrfToken ??= new ZfToken();

        if ($container->has(AuthenticationService::class)) {
            $authen = $container->get(AuthenticationService::class)->getIdentity();
            $configs = $container->get('config');
            if (!empty($authen)) {
                if (isset($configs['csrf_token'], $configs['csrf_token'][APPLICATION_SITE])) {
                    $this->_userFolder = $authen->{$configs['csrf_token'][APPLICATION_SITE]} ?? '';
                } else {
                    $this->_userFolder = $authen->user_code ?? $authen->admin_code ?? '';
                }
            } else {
                $this->_userFolder = '';
            }
            unset($configs, $authen);
        }
    }

    public function __invoke(): self
    {
        return $this;
    }

    /**
     * Create CSRF token
     * 
     * @param array $unique
     * @param string|null $userFolder
     * @return string
     */
    public function generalCsrfToken(array $unique = [], ?string $userFolder = null): string
    {
        $userFolder ??= $this->_userFolder;
        return self::$csrfToken->generalCsrfToken($userFolder, $unique);
    }
}
