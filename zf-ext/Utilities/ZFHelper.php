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
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
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

    /**
     * Random string
     * 
     * @param number $length            
     * @param bool $useSpecialChar            
     * @param bool $userNum            
     * @return string
     */
    public function getRandomString(int $length = 10, bool $useSpecialChar = false, bool $userNum = true): string
    {
        if ($length <= 0)
            return '';

        $specialChar = '`!@#&$%^*-_+={}[];?<>,.|';
        $string = ($userNum ? '0123456789' : '') . "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" . ($useSpecialChar ? $specialChar : '');
        return substr(str_shuffle($string), 0, $length);
    }

    /**
     * Generates a random code with specified options.
     *
     * @param array $options Options to generate random code. Possible options are:
     *   - id: The unique identifier to be used in generating the code. Defaults to `memory_get_usage(true) + time()`.
     *   - maxLen: Maximum length of the code. Defaults to 15.
     *   - toBase: The base to use in converting the ID to a string. Defaults to 32.
     *   - isSpecialChar: Whether to include special characters in the code. Defaults to false.
     *
     * @return string The randomly generated code.
     */
    public function getRandomCode(array $options = []): string
    {
        $options = array_merge([
            'id' => memory_get_usage(true) + time(),
            'maxLen' => 15,
            'toBase' => 32,
            'isSpecialChar' => false,
        ], $options);

        if ($options['maxLen'] <= 0) {
            return '';
        }

        $idString = base_convert($options['id'], 10, $options['toBase']);
        $idStringLength = strlen($idString);
        $codeLength = $options['maxLen'] - $idStringLength;

        if ($codeLength > 0) {
            $randomString = $this->getRandomString($codeLength, $options['isSpecialChar']);
            $randomStringArray = str_split($randomString);
            $idStringArray = str_split($idString);
            $minLength = min($codeLength, $idStringLength);
            $result = [];
            $no = 0;

            while ($no < $minLength) {
                $result[] = array_pop($randomStringArray);
                $result[] = array_pop($idStringArray);
                $no++;
            }

            $code = implode('', $result);

            if (count($randomStringArray) > 0) {
                $code .= implode('', $randomStringArray);
            } else {
                $code .= implode('', $idStringArray);
            }
        } else {
            $code = $idString;
        }

        return substr($code, 0, $options['maxLen']);
    }

}