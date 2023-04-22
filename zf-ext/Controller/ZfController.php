<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Doctrine\ORM\Query\Expr\Func;
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
     * @param string $key The header key to get the value of
     * @return mixed|null The value of the specified header key, or null if it does not exist
     */
    public function getParamHeader(string $key = ''): mixed
    {
        return $this->params()->fromHeader($key)->getFieldValue() ?? null;
    }

    /**
     * Custom get param payload
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParamsPayload(string $key = null, mixed $default = null): mixed
    {
        $payload = $this->getRequest()->getContent();
        return @json_decode($payload, true)[$key] ?? $default;
    }

    /**
     * Custom get param from post method
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParamsPost(string $key = null, mixed $default = null): mixed
    {
        return $this->params()->fromPost($key, $default);
    }

    /**
     * Custom get param from query
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParamsQuery(string $key = null, mixed $default = null): mixed
    {
        return $this->params()->fromQuery($key, $default);
    }

    /**
     * Custom get param from route
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParamsRoute(string $key = null, mixed $default = null): mixed
    {
        return $this->params()->fromRoute($key, $default);
    }

    /**
     * Custom get param from file
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParamsFiles(string $key = null, mixed $default = null): mixed
    {
        return $this->params()->fromFiles($key, $default);
    }

    /**
     * Custom is post request
     * @return bool
     */
    public function isPostRequest(): bool
    {
        return $this->getRequest()->isPost();
    }

    /**
     * Custom redirect to route
     * @param string $routeName
     * @param array $params
     * @param array $options
     * 
     */
    public function redirectToRoute(string $routeName = '', array $params = [], array $options = []): mixed
    {
        $routeName = $routeName ?? $this->getCurrentRouteName();
        return $this->zfRedirect()->toRoute($routeName, $params, $options);
    }

    /**
     * Custom redirect to url
     * @param string $url
     */
    public function redirectToUrl(string $url = ''): mixed
    {
        return $this->zfRedirect()->toUrl($url);
    }

    /**
     * Common helper
     * @var \Zf\Ext\Utilities\ZFHelper
     */
    private static ?ZFHelper $_common = null;

    /**
     * Get the ZF Helper instance
     * @return ZFHelper The ZF Helper instance
     */
    public function getZfHelper(): ZFHelper
    {
        return self::$_common ??= new ZFHelper();
    }

    /**
     * Get host by IP address
     * @param string $ip The IP address to get the host from
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
     * Custom flash error's message
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
     * Custom flash success's message
     * @param string $msg
     * @return void
     */
    public function addSuccessMessage(string $msg = ''): void 
    {
        $this->flashMessenger()->addSuccessMessage(
            $this->mvcTranslate($msg)
        );
    }

    /**
     * Custom get repository entityManager
     * 
     * @param Models\Entities $entityName
     * @param string $connectionName
     * @return \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     */
    public function getEntityRepo($entityName, $connectionName = 'orm_default')
    {
        return $this->getEntityManager($connectionName)->getRepository($entityName);
    }

    /**
     * Custom get connection of entityManager
     * @param string $connectionName
     * @return \Doctrine\DBAL\Connection
     */
    public function getEntityConnection(string $connectionName = 'orm_default')
    {
        return $this->getEntityManager($connectionName)->getConnection();
    }

    /**
     * Custom entityManager rollback
     * @param string $connectionName
     * @return void
     */
    public function rollbackTransaction(string $connectionName = 'orm_default'): void
    {
        $this->getEntityManager($connectionName)->rollback();
    }

    /**
     * Custom entityManager commit
     * @param string $connectionName
     * @return void
     */
    public function commitTransaction(string $connectionName = 'orm_default'): void
    {
        $this->getEntityManager($connectionName)->commit();
    }

    /**
     * Custom entityManager commit
     * @param string $connectionName
     * @return void
     */
    public function startTransaction(string $connectionName = 'orm_default'): void
    {
        $this->getEntityManager($connectionName)->beginTransaction();
    }

    /**
     * Custom create CSRF token
     *
     * @param array $unique
     * @param string|null $userFolder
     * @param string|null $site
     * @param int $lifetime
     * @return string
     */
    public function generateCsrfToken(
        array $unique = [], 
        ?string $userFolder = null, 
        ?string $site = null, 
        int $lifetime = 86400): string 
    {
        return $this->zfCsrfToken()->generateCsrfToken($unique, $userFolder, $site, $lifetime);
    }

    /**
     * Custom check CSRF token
     *
     * @param string|null $token
     * @param string|null $userFolder
     * @param int $lifetime
     * @param string|null $site
     * @return bool true if token is valid
     */
    public function isValidCsrfToken(
        ?string $token = null, 
        ?string $userFolder = null, 
        int $lifetime = 86400, 
        ?string $site = null): bool
    {
        return $this->zfCsrfToken()->isValidCsrfToken($token, $userFolder, $lifetime, $site);
    }

    /**
     * Custom clear CSRF token
     *
     * @param string|null $token
     * @param string|null $userFolder
     * @param string|null $site
     * @return bool true if success
     */
    public function clearCsrfToken(
        ?string $token = null, 
        ?string $userFolder = null, 
        ?string $site = null): bool
    {
        return $this->zfCsrfToken()->clearCsrfToken($token, $userFolder, $site);
    }

    /**
     * Get current ID of login user
     *
     * @return mixed
     */
    public function getLoginId(): mixed
    {
        $key = $this->getAuthen()->authen_key;
        return intval($this->getAuthen()->{$key} ?? 0);
    }
}
