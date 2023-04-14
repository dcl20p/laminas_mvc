<?php
namespace Zf\Ext\Utilities;

use Zf\Ext\Utilities\ZFFilterSpecialChar;
use Zf\Ext\Utilities\ZFFilterUnicode;

class ZFHelper
{
    /**
     *
     * @var ZFFilterSpecialChar
     */
    protected $noSpecialChar;

    /**
     *
     * @var ZFFilterUnicode
     */
    protected $noUnicode;

    /**
     * Contructor
     */
    public function __construct()
    {
        $this->noSpecialChar = new ZFFilterSpecialChar();
        $this->noUnicode = new ZFFilterUnicode();
    }

    public function getClientIp(): ?string
    {
        $ipAddress = '';
        foreach ([
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ] as $key) {
            if (isset($_SERVER[$key])) {
                $ipAddress = $_SERVER[$key];
                break;
            }
        }

        $ip = filter_var($ipAddress, FILTER_VALIDATE_IP);
        if ($ip !== false) {
            return $ip;
        }
        return preg_replace('/[^a-z0-9\.\:]/i', '', $ipAddress);
    }
}