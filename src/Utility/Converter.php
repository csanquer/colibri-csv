<?php

namespace CSanquer\ColibriCsv\Utility;

/**
 * Converter Utility class based on code from PHPExcel_Shared_String
 *
 * @codeCoverageIgnore
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class Converter
{
    /**
     * Is mbstring extension available?
     *
     * @var boolean
     */
    private static $mbstringEnabled;

    /**
     * Is iconv extension available?
     *
     * @var boolean
     */
    private static $iconvEnabled;

    /**
     * detect if mbstring is available
     *
     * @return boolean
     *
     * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
     * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
     */
    public static function isMbstringEnabled()
    {
        if (isset(self::$mbstringEnabled)) {
            return self::$mbstringEnabled;
        }

        self::$mbstringEnabled = function_exists('mb_convert_encoding');

        return self::$mbstringEnabled;
    }

    /**
     * detect if iconv is available
     *
     * @return boolean
     *
     * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
     * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
     */
    public static function isIconvEnabled()
    {
        if (isset(self::$iconvEnabled)) {
            return self::$iconvEnabled;
        }

        // Fail if iconv doesn't exist
        if (!function_exists('iconv')) {
            self::$iconvEnabled = false;

            return self::$iconvEnabled;
        }

        // Sometimes iconv is not working, and e.g. iconv('UTF-8', 'UTF-16LE', 'x') just returns false,
        if (!@iconv('UTF-8', 'UTF-16LE', 'x')) {
            self::$iconvEnabled = false;

            return self::$iconvEnabled;
        }

        // CUSTOM: IBM AIX iconv() does not work
        if (defined('PHP_OS') &&
            @stristr(PHP_OS, 'AIX') &&
            defined('ICONV_IMPL') &&
            @strcasecmp(ICONV_IMPL, 'unknown') == 0 &&
            defined('ICONV_VERSION') &&
            @strcasecmp(ICONV_VERSION, 'unknown') == 0
        ) {
            self::$iconvEnabled = false;

            return self::$iconvEnabled;
        }

        // If we reach here no problems were detected with iconv
        self::$iconvEnabled = true;

        return self::$iconvEnabled;
    }

    /**
     *
     * @param  string $str
     * @param  string $fallback
     * @return string
     */
    public static function detectEncoding($str, $fallback = 'UTF-8')
    {
        $encoding = null;
        if (self::isMbstringEnabled()) {
            $encodingList = array(
                'ASCII',
                'UTF-8',
                'ISO-8859-1',
                'ISO-8859-2',
                'ISO-8859-3',
                'ISO-8859-4',
                'ISO-8859-5',
                'ISO-8859-6',
                'ISO-8859-7',
                'ISO-8859-8',
                'ISO-8859-9',
                'ISO-8859-10',
                'ISO-8859-13',
                'ISO-8859-14',
                'ISO-8859-15',
                'ISO-8859-16',
                'Windows-1251',
                'Windows-1252',
                'Windows-1254',
            );

            $encoding = mb_detect_encoding($str, $encodingList, true);
        }

        return $encoding ? $encoding : $fallback;
    }

    /**
     * Convert string from one encoding to another. First try iconv, then mbstring, or no convertion
     *
     * @param  string $value
     * @param  string $from          default = auto Encoding to convert from, e.g. 'UTF-16LE'
     * @param  string $to            default = UTF-8  Encoding to convert to, e.g. 'UTF-8'
     * @param  string $iconvTranslit default = null Iconv translit option possible values : 'translit', 'ignore', null
     * @return string
     *
     * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
     * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
     */
    public static function convertEncoding($value, $from = 'auto', $to = 'UTF-8', $iconvTranslit = null)
    {
        if ($from != $to) {
            if ($from == 'auto' || empty($from)) {
                $from = self::detectEncoding($value);
            }

            if (self::isIconvEnabled()) {
                $iconvTranslit = strtoupper($iconvTranslit);
                $iconvTranslit = in_array($iconvTranslit, array('TRANSLIT', 'IGNORE')) ? '//'.$iconvTranslit : '';
                $value = iconv($from, $to.$iconvTranslit, $value);

                return $value;
            }

            if (self::isMbstringEnabled()) {
                $value = mb_convert_encoding($value, $to, $from);

                return $value;
            }
            if ($from == 'UTF-16LE') {
                return self::utf16Decode($value, false);
            } elseif ($from == 'UTF-16BE') {
                return self::utf16Decode($value);
            }
        }

        return $value;
    }

    /**
     * Decode UTF-16 encoded strings.
     *
     * Can handle both BOM'ed data and un-BOM'ed data.
     * Assumes Big-Endian byte order if no BOM is available.
     * This function was taken from http://php.net/manual/en/function.utf8-decode.php
     * and $bom_be parameter added.
     *
     * @param  string $str UTF-16 encoded data to decode.
     * @return string UTF-8 / ISO encoded data.
     * @access  public
     * @version 0.2 / 2010-05-13
     * @author  Rasmus Andersson {@link http://rasmusandersson.se/}
     * @author vadik56
     */
    public static function utf16Decode($str, $bom_be = true)
    {
        if (strlen($str) < 2) {
            return $str;
        }
        $c0 = ord($str{0});
        $c1 = ord($str{1});
        if ($c0 == 0xfe && $c1 == 0xff) {
            $str = substr($str, 2);
        } elseif ($c0 == 0xff && $c1 == 0xfe) {
            $str = substr($str, 2);
            $bom_be = false;
        }
        $len = strlen($str);
        $newstr = '';
        for ($i = 0; $i < $len; $i+=2) {
            if ($bom_be) {
                $val = ord($str{$i}) << 4;
                $val += ord($str{$i + 1});
            } else {
                $val = ord($str{$i + 1}) << 4;
                $val += ord($str{$i});
            }
            $newstr .= ($val == 0x228) ? "\n" : chr($val);
        }

        return $newstr;
    }
}
