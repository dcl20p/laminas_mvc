<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Zf\Ext\Utilities\ZFHelper;
/**
 * allow specifying status code as a default, or as an option to methods
 */
class ZfController extends AbstractActionController
{
    /**
     * Default message on error occurred
     */
    const DEFAULT_ERROR_MSG = 'Some error occurred';

    /**
     * @param Get match route name
     * @return string
     */
    public function getCurrentRouteName()
    {
        return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
    }

    /**
     * Save error log
     *
     * @param \Throwable $exception The exception object to be logged
     * @return void
     * @throws \RuntimeException
     */
    public function saveErrorLog(\Throwable $exception): void
    {
        $user = $this->getAuthen();
        $params = [
            'post' => $this->params()->fromPost(),
            'get' => $this->params()->fromQuery(),
        ];

        foreach ($params as $key => $items) {
            $params[$key] = $this->makeParamsForInsert($items);
        }

        $url = mb_substr((string)$this->getRequest()->getUri()->getPath(), 0, 200);
        $msg = $exception->getMessage();

        dd($msg, $exception->getTraceAsString());

        // $this->sendMailWarningError("Uri: {$url}<br>Message: {$msg}");

        // $entityManager = $this->getEntityManager();
        // $connection = $entityManager->getConnection();

        // $pathApp = realpath(APPLICATION_PATH . '/../../');
        // $pathLib = realpath(LIBRARY_PATH . '/../');
        // $pathPub = realpath(PUBLIC_PATH . '/../');

        // try {
        //     $connection->insert('tbl_error', [
        //         'error_user_id' => $user ? ($user->{$user->authen_key} ?? null) : null,
        //         'error_uri' => $url,
        //         'error_params' => $params,
        //         'error_method' => $this->getRequest()->getMethod(),
        //         'error_msg' => 'Message: ' . str_replace([$pathApp, $pathPub, $pathLib], '', substr($msg, 0, 2000))
        //             . ".\nOn line: " . $exception->getLine()
        //             . ".\nOf file: " . str_replace([$pathApp, $pathPub, $pathLib], '', $exception->getFile()),
        //         'error_trace' => str_replace([$pathApp, $pathPub, $pathLib], '', substr($exception->getTraceAsString(), 0, 6000)),
        //         'error_code' => $exception->getCode(),
        //         'error_time' => time()
        //     ]);
        // } catch (\Throwable $e) {
        //     throw new \RuntimeException('Unable to save error log: ' . $e->getMessage());
        // }
    }

    /**
     * Truncate data before insert to database
     * @param array $items
     * @return array
     */
    protected function makeParamsForInsert(array $items): array
    {
        foreach ($items as $idx => $item) {
            if (is_array($item))
                $items[$idx] = $this->makeParamsForInsert($item);
            elseif (is_string($item))
                $items[$idx] = htmlspecialchars(trim(mb_substr($item, 0, 255)));
        }
        return $items;
    }

    /**
     * Get device information
     *
     * @return array Device information array
     */
    public function getDevice(): array
    {
        $default = [
            'browser'       => 'UNKNOWN',
            'agent'         => $this->getParamHeader('user-agent'),
            'device'        => 'UNKNOWN',
            'version'       => 'UNKNOWN',
            'type'          => 'UNKNOWN',
            'os'            => 'UNKNOWN',
            'os_version'    => 'UNKNOWN',
            'ip_address'    => $ip = $this->getZfHelper()->getClientIp(),
            'hostname'      => $this->getHostByIP($ip)
        ];

        try {
            $matomoParser = new \DeviceDetector\DeviceDetector(
                $default['agent'] ?? ''
            );

            $matomoParser->setCache(
                new \DeviceDetector\Cache\DoctrineBridge(
                    new \Doctrine\Common\Cache\PhpFileCache( DATA_PATH . '/cache/matomo')
                )
            );
            
            $matomoParser->parse();
            
            if( $matomoParser->isBot() === true ) {
                $botInfo = $matomoParser->getBot();
                $default['browser'] = $botInfo['name'] ?? '';
                $default['os'] = $botInfo['category'] ?? '';
                $default['device'] = 'BOT';
            }
            else{
                $client = $matomoParser->getClient();
                $osParse = $matomoParser->getOs();
                $default['browser'] = $client['name'] ?? '';
                $default['version'] = $client['version'] ?? '';
                
                $osName = $osParse['name'] ?? '';
                if ( $osName ){
                    $default['os'] = $osName;
                    $default['os_version'] = $osParse['version'] ?? '';
                }
                $device = $matomoParser->getDeviceName();
                if ( $device ){
                    $default['device'] = $device;
                }
                if ( !empty($client['type']) ){
                    $default['type'] = strtoupper($client['type']);
                }
            }

        } catch (\Throwable $e) {
            $this->saveErrorLog($e);
        }
        
        return $default;
    }


    /**
     * Get the value of the specified header key
     *
     * @param string $key The header key to get the value of
     *
     * @return mixed|null The value of the specified header key, or null if it does not exist
     */
    public function getParamHeader(string $key = ''): mixed
    {
        return $this->params()->fromHeader($key)->getFieldValue() ?? null;
    }

    /**
     * Common helper
     * @var \Zf\Ext\Utilities\ZFHelper
     */
    private static ?ZFHelper $_common = null;

    /**
     * Get the ZF Helper instance
     *
     * @return ZFHelper The ZF Helper instance
     */
    public function getZfHelper(): ZFHelper
    {
        return self::$_common ??= new ZFHelper();
    }

    /**
     * Get host by IP address
     * 
     * @param string $ip The IP address to get the host from
     * 
     * @return string The host or IP address if host is not available
     */
    protected function getHostByIP(string $ip): string
    {
        $host = gethostbyaddr($ip) ?? $ip;
        // Get provider
        $host = preg_replace('/(\d+\.)/', '', $host);

        return empty($host) ? $ip : $host;
    }

    /**
     * Add error flash message
     *
     * @param string $msg
     * @return void
     */
    public function addErrorMessage(string $msg = ''): void 
    {
        $this->flashMessenger()->addErrorMessage(
            $this->mvcTranslate($msg)
        );
    }

    /**
     * Add success flash message
     *
     * @param string $msg
     * @return void
     */
    public function addSuccessMessage(string $msg = ''): void 
    {
        $this->flashMessenger()->addSuccessMessage(
            $this->mvcTranslate($msg)
        );
    }

}
