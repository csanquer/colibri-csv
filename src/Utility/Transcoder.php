<?php

namespace CSanquer\ColibriCsv\Utility;

use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;
use Ddeboer\Transcoder\IconvTranscoder;
use Ddeboer\Transcoder\MbTranscoder;
use Ddeboer\Transcoder\TranscoderInterface;
use Ddeboer\Transcoder\Transcoder as BaseTranscoder;

/**
 * Transcoder : Charset and encoding manager class
 * Adapter class based on Ddeboer\Transcoder\TranscoderInterface
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class Transcoder implements TranscoderInterface
{
    /**
     * @var TranscoderInterface
     */
    protected $transcoder;

    /**
     * Is mbstring extension available?
     *
     * @var boolean
     */
    protected $mbstringEnabled;

    /**
     * Is iconv extension available?
     *
     * @var boolean
     */
    protected $iconvEnabled;

    /**
     * @var array
     */
    protected $bomList = [
        'UTF-7' => [
            "\x2B\x2F\76\x38",
            "\x2B\x2F\76\x39",
            "\x2B\x2F\76\x2B",
            "\x2B\x2F\76\x2F",
        ],
        'UTF-8' => "\xEF\xBB\xBF",
        'UTF-16BE' => "\xFE\xFF",
        'UTF-16LE' => "\xFF\xFE",
        'UTF-32BE' => "\x00\x00\xFE\xFF",
        'UTF-32LE' => "\xFF\xFE\x00\x00",
    ];

    /**
     * @param string $defaultEncoding
     * @param bool $forceMbString
     */
    public function __construct($defaultEncoding = 'UTF-8', $forceMbString = false)
    {
        $this->iconvEnabled = function_exists('iconv');
        $this->mbstringEnabled = function_exists('mb_convert_encoding');

        $defaultEncoding = empty($defaultEncoding) ? 'UTF-8' : $defaultEncoding;

        if ($this->iconvEnabled && !$forceMbString) {
            $this->transcoder = new IconvTranscoder($defaultEncoding);
        } elseif ($this->mbstringEnabled) {
            $this->transcoder = new MbTranscoder($this->getWindowsCPEncoding($defaultEncoding));
        }
    }

    /**
     *
     * @param  string $str
     * @param  string $fallback
     * @return string
     */
    public function detectEncoding($str, $fallback = 'UTF-8')
    {
        $encoding = null;
        if ($this->mbstringEnabled) {
            $encodingList =[
                'ASCII',
                'UTF-8',
                'UTF-16BE',
                'UTF-16LE',
                'UTF-32BE',
                'UTF-32LE',
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
                'UTF-7',
            ];

            $encoding = mb_detect_encoding($str, $encodingList, true);
        }

        return $encoding ? $encoding : $fallback;
    }

    /**
     * Transcode a string from one into another encoding
     *
     * @param string $string String
     * @param string $from From encoding (optional) default = auto
     * @param string $to To encoding (optional) default = UTF-8
     * @param string $iconvTranslit (optional) default = null Iconv translit option possible values : 'translit', 'ignore', null
     *
     * @return string
     *
     * @throws UnsupportedEncodingException
     */
    public function transcode($string, $from = 'auto', $to = 'UTF-8', $iconvTranslit = null)
    {
        if ($this->transcoder && $from != $to) {
            if ($from == 'auto' || empty($from)) {
                $from = $this->detectEncoding($string);
            }

            if ($this->transcoder instanceof IconvTranscoder) {
                $iconvTranslit = strtoupper($iconvTranslit);
                $to .= in_array($iconvTranslit, ['TRANSLIT', 'IGNORE']) ? '//'.$iconvTranslit : '';
            } elseif ($this->transcoder instanceof MbTranscoder) {
                $from = $this->getWindowsCPEncoding($from);
                $to = $this->getWindowsCPEncoding($to);
            }

            $string = $this->transcoder->transcode($string, $from, $to);
        }

        return $string;
    }

    /**
     * get BOM for given encoding
     *
     * @param string $encoding
     * @return string BOM
     */
    public function getBOM($encoding = 'UTF-8')
    {
        return isset($this->bomList[$encoding]) ? $this->bomList[$encoding] : null;
    }

    /**
     * get Valid Windows CP encoding name for mb_string
     *
     * @param $encoding
     * @return string
     */
    protected function getWindowsCPEncoding($encoding)
    {
        return in_array($encoding, ['CP1251', 'CP1252', 'CP1254']) ? 'Windows-'.substr($encoding, 2, 4) : $encoding;
    }
}
