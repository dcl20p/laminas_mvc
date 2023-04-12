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

/**
 * @todo       allow specifying status code as a default, or as an option to methods
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
}
