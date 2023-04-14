<?php
namespace Zf\Ext\Utilities;

use Laminas\Filter\FilterInterface;

/**
 * Filter out special characters from a string
 */
class ZFFilterSpecialChar implements FilterInterface
{
    /**
     * Array of special characters to remove
     * 
     * @var array
     */
    protected $arrSpecialChar = ["`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "+", "(", ")", "|", "{", "}", "[", "]", "\\", "'", "\"", "/", ",", "."];

    /**
     * Filter out special characters from a string
     * 
     * @param mixed $value The input string to filter
     * 
     * @return mixed The filtered string
     */
    public function filter(mixed $value): mixed
    {
        return str_replace($this->arrSpecialChar, "", $value);
    }
}
