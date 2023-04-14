<?php
namespace Zf\Ext\Utilities;

use Laminas\Filter\FilterInterface;

/**
 * Class ZFFilterUnicode
 *
 * A filter to convert Unicode characters to ASCII characters.
 *
 * @package MyPackage
 */
class ZFFilterUnicode implements FilterInterface
{
    /**
     * Separator character used to replace space characters.
     *
     * @var string
     */
    protected $separator = '_';

    /**
     * Map of Unicode characters and their corresponding ASCII characters.
     *
     * @var array
     */
    protected $arrUnicode = [
        "a" => [
            "á",
            "à",
            "ả",
            "ã",
            "ạ",
            "ă",
            "ắ",
            "ằ",
            "ẳ",
            "ẵ",
            "ặ",
            "â",
            "ấ",
            "ầ",
            "ẩ",
            "ẫ",
            "ậ"
        ],
        "A" => [
            "Á",
            "À",
            "Ả",
            "Ã",
            "Ạ",
            "Ă",
            "Ắ",
            "Ằ",
            "Ẳ",
            "Ẵ",
            "Ặ",
            "Â",
            "Ấ",
            "Ầ",
            "Ẩ",
            "Ẫ",
            "Ậ"
        ],
        "d" => [
            "đ"
        ],
        "D" => [
            "Đ"
        ],
        "e" => [
            "é",
            "è",
            "ẻ",
            "ẽ",
            "ẹ",
            "ê",
            "ề",
            "ế",
            "ể",
            "ễ",
            "ệ"
        ],
        "E" => [
            "É",
            "È",
            "Ẻ",
            "Ẽ",
            "Ẹ",
            "Ê",
            "Ề",
            "Ế",
            "Ể",
            "Ễ",
            "Ệ"
        ],
        "i" => [
            "í",
            "ì",
            "ỉ",
            "ĩ",
            "ị"
        ],
        "I" => [
            "Í",
            "Ì",
            "Ỉ",
            "Ĩ",
            "Ị"
        ],
        "o" => [
            "ó",
            "ò",
            "ỏ",
            "õ",
            "ọ",
            "ô",
            "ố",
            "ồ",
            "ổ",
            "ỗ",
            "ộ",
            "ơ",
            "ớ",
            "ờ",
            "ở",
            "ỡ",
            "ợ"
        ],
        "O" => [
            "Ó",
            "Ò",
            "Ỏ",
            "Õ",
            "Ọ",
            "Ô",
            "Ố",
            "Ồ",
            "Ổ",
            "Ỗ",
            "Ộ",
            "Ơ",
            "Ớ",
            "Ờ",
            "Ở",
            "Ỡ",
            "Ợ"
        ],
        "u" => [
            "ú",
            "ù",
            "ủ",
            "ũ",
            "ụ",
            "ư",
            "ứ",
            "ừ",
            "ử",
            "ữ",
            "ự"
        ],
        "U" => [
            "Ú",
            "Ù",
            "Ủ",
            "Ũ",
            "Ụ",
            "Ư",
            "Ứ",
            "Ừ",
            "Ử",
            "Ữ",
            "Ự"
        ],
        "y" => [
            "ý",
            "ỳ",
            "ỷ",
            "ỹ",
            "ỵ" 
        ],
        "Y" => [
            "Ý",
            "Ỳ",
            "Ỷ",
            "Ỹ",
            "Ỵ" 
        ]
    ];
    
    /**
     * Set the separator character.
     *
     * @param string $charSpecial The separator character.
     *
     * @return $this
     */
    public function setCharSpecial(string $charSpecial)
    {
        $this->separator = $charSpecial;

        return $this;
    }

    /**
     * Get the separator character.
     *
     * @return string The separator character.
     */
    public function getCharSpecial(): string
    {
        return $this->separator;
    }

    /**
     * Filter a string, converting Unicode characters to ASCII characters.
     *
     * @param mixed $value The input string.
     *
     * @return mixed The filtered string.
     */
    public function filter(mixed $value): mixed
    {
        $arrSpecialSymbol = [
            "\n",
            "\t",
            "\r",
            "\n\r",
            "\r\n",
        ];
        $value = str_replace($arrSpecialSymbol, '', $value);
    
        $unicodeMap = array_flip($this->arrUnicode);
        $value = strtr($value, $unicodeMap);
    
        $value = str_replace(' ', $this->separator, $value);
    
        return $value;
    }
}