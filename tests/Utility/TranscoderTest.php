<?php

namespace CSanquer\ColibriCsv\Tests\Utility;

use CSanquer\ColibriCsv\Utility\Transcoder;
use PHPUnit\Framework\TestCase;

class TranscoderTest extends TestCase
{

    /**
     * @dataProvider providerTranscode
     */
    public function testTranscode($defaultEncoding, $forceMbString, $from, $to, $iconvTranslit, $string, $expected)
    {
        $transcoder = new Transcoder($defaultEncoding, $forceMbString);
        $this->assertEquals($expected, $transcoder->transcode($string, $from, $to, $iconvTranslit));
    }

    public function providerTranscode()
    {
        return [
            // dataset #0
            [
                'UTF-8',
                true,
                'CP1252',
                'UTF-8',
                'translit',
                mb_convert_encoding('prénom', 'Windows-1252', 'UTF-8'),
                'prénom',
            ]
        ];
    }
}
