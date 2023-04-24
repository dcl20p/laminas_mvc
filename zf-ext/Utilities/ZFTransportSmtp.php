<?php
namespace Zf\Ext\Utilities;

use Doctrine\DBAL\Connection;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as TransPortSmtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mime\Message as MimeMessage;

class ZFTransportSmtp
{
    /**
     *
     * @var Zend_Mail_Transport_Smtp
     */
    protected static $_transport = null;

    /**
     *
     * @var array
     */
    protected static array $_config = [];

    /**
     * Create send mail transport
     *
     * @param array $config
     * @param  \Laminas\Db\Adapter\Adapter $zAdapter
     * @return TransPortSmtp
     */
    protected static function getTransport(
        array $config = [], 
        $zAdapter = null, 
        string $tbl = 'tbl_send_mail'
        ): TransPortSmtp 
    {
        if (empty(self::$_transport)) {
            if (empty($config)) {
                // Create sql service
                $sqlService = new Sql($zAdapter);

                $sql = $sqlService->select()
                    ->from('tbl_constant')
                    ->columns([
                        'constant_content'
                ])
                    ->where([
                        'constant_code = ?' => 'system_server_mail_config'
                ]);

                $selectString = $sql->getSqlString($zAdapter->getPlatform());
                $config = $zAdapter->query($selectString, $zAdapter::QUERY_MODE_EXECUTE)
                    ->execute();

                if ($config) {
                    $config = @json_decode($config['constant_content'], true);
                } else $config = [];

                $sql = $sqlService->select()
                    ->from($tbl)
                    ->columns([
                        'send_mail_id',
                        'send_mail_account',
                        'send_mail_password'
                    ])
                    ->order('send_mail_total ASC')
                    ->limit(1)
                    ->offset(0);

                $selectString = $sqlService->buildSqlString($sql, $zAdapter);
                $sendMail = $zAdapter->query($selectString, $zAdapter::QUERY_MODE_EXECUTE)
                    ->current();

                // Plus send mail times
                $update = $sqlService->update($tbl);
                $update->set([
                    'send_mail_total' => new Expression('`send_mail_total` + 1')
                ])->where([
                    'send_mail_id' => $sendMail['send_mail_id']
                ]);
                $sqlService->prepareStatementForSqlObject($update)->execute();

                $config = array_merge($config, [
                    'username' => $sendMail['send_mail_account'],
                    'password' => $sendMail['send_mail_password'],
                ]);
            }

            $config['use_complete_quit'] = true;
            self::$_config = $config;

            $host = $config['host'];
            unset($config['host']);

            $smtpOptions = new SmtpOptions();
            $smtpOptions->setHost($host)
                ->setName($host)
                ->setConnectionClass($config['auth'])
                ->setPort((int) $config['port'])
                ->setConnectionConfig($config);
            
            self::$_transport = new TransPortSmtp($smtpOptions);
            unset($smtpOptions, $config);
        }
        return self::$_transport;
    }

    /**
     * Get mail server config
     *
     * @param Connection|null $dtAdapter
     * @return array
     */
    protected static function getDbConfigByDt(?Connection $dtAdapter = null): array
    {
        return @json_decode(
            $dtAdapter->fetchOne("SELECT fnc_getMailConfigs()") ?? '{}', true
        ) ?? [];
    }

    /**
     * Send mail
     *
     * @param \Laminas\Mail\Transport\Smtp $transport
     * @param array $options
     * @return void
     */
    public static function sendMsg(TransPortSmtp $transport, array $options): void
    {
        $message = new Message();

        if (!empty($options['replyTo']))
            $message->setReplyTo($options['replyTo']);
        if (!empty($options['from']))
            $message->setFrom($options['from']);

        // Create HTML message
        $html = new MimePart($options['msg']);
        $html->setType(Mime::TYPE_HTML);
        $html->setCharset('UTF-8');
        $html->setEncoding(
            $options['encoding'] ?? Mime::ENCODING_QUOTEDPRINTABLE
        );
        $body = new MimeMessage();
        $body->addPart($html);
        unset($html);

        if (!empty($options['attachment'])) {
            foreach ($options['attachment'] as $file) {
                $filePath = $file['fullPath'] ?? $file['tmp_name'];
                $file['type'] = $file['type'] ?? filetype($filePath);

                $attachment = new MimePart(file_get_contents($filePath));
                $attachment->type = $file['type'];
                $attachment->filename = basename(self::noMark($file['name']));
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Mime::ENCODING_BASE64;
                $body->addPart($attachment);
            }
            unset($attachment);
        }
        $message->setEncoding('UTF-8');
        $message->setBody($body);
        unset($body);

        if (!empty($options['to']))
            $message->addTo($options['to'], $options['toName'] ?? '');
        $message->setSubject($options['title'] ?? '');
        if (!empty($options['cc']))
            $message->addCc($options['cc'], $options['ccName'] ?? '');
        if (!empty($options['bcc']))
            $message->addBcc($options['bcc'], $options['bccName'] ?? '');

        $transport->send($message);
        unset($message);
    }

    /**
     * Escape Vietnam char
     *
     * @param string $str
     * @return string
     */
    public static function noMark(string $str): string
    {
        if (empty($str)) return '';

        $utf8 = [
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'D' => 'Đ',
            'd' => 'đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ'
        ];

        foreach ($utf8 as $ascii => $unicode) {
            $str = preg_replace("/($unicode)/", $ascii, $str);
        }

        return $str;
    }

    /**
     * Send mail
     *
     * @param array $options
     *  <p>string title: subject of mail</p>
     *  <p>string toName: name of receiver</p>
     *  <p>string from: send from email</p>
     *  <p>string to: send to email</p>
     *  <p>string msg: body of email</p>
     *  <p>array attachment: list of file want to send</p>
     * @param Connection|null $dtAdapter
     * @return boolean
     */
    public static function sendMail(array $options, ?Connection $dtAdapter = null): bool
    {
        try {
            self::sendMsg(self::getTransport(
                    self::getDbConfigByDt($dtAdapter)
                ), $options
            );
            return true;
        } catch (\Throwable $e) {
            self::_logError($e->getMessage() . "\n" . $e->getTraceAsString(), $dtAdapter);
        }
        return false;
    }

    /**
     * Save log
     *
     * @param string $msg
     * @param Connection|null $dtAdapter
     * @return void
     */
    protected static function _logError(string $msg, ?Connection $dtAdapter = null): void
    {
        try {
            // Use doctrine
            if ($dtAdapter instanceof Connection) {
                $dtAdapter->insert(
                    'tbl_log_error_sendmail', [
                    'log_error_email'   => self::$_config['username'] ?? '',
                    'log_error_content' => $msg,
                    'log_error_time'    => time()
                ]);
            } else {
                $sqlService = new Sql($dtAdapter);
                $insert = $sqlService->insert('tbl_log_error_sendmail');
                $insert->columns([
                    'log_error_email',
                    'log_error_content',
                    'log_error_time'
                ])->values([
                    'log_error_email' => self::$_config['username'] ?? '',
                    'log_error_content' => $msg,
                    'log_error_time' => time()
                ]);
                $sqlService->prepareStatementForSqlObject($insert)->execute();
                unset($sqlService, $insert);
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to save error log: ' . $e->getMessage());
        }
    }
}