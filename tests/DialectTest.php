<?php

namespace CSanquer\ColibriCsv\Tests;

use Doctrine\Common\Inflector\Inflector;
use CSanquer\ColibriCsv\Dialect;
use CSanquer\ColibriCsv\Tests\AbstractCsvTestCase;

/**
 * DialectTest
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class DialectTest extends AbstractCsvTestCase
{
    /**
     *
     * @var Dialect
     */
    protected $dialect;

    protected function setUp()
    {
        $this->dialect = new Dialect();
    }

    /**
     * @dataProvider providerConstruct
     */
    public function testConstruct($options, $expected)
    {
        $dialect = new Dialect($options);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, call_user_func(array($dialect, Inflector::camelize('get_'.$key))), 'the value is not the expected for the option '.$key);
        }

    }

    public function providerConstruct()
    {
        return array(
            array(
                array(),
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'CP1252',
                    'line_endings' => "\r\n",
                    'escape' => "\\",
                    'use_bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detection' => false,
                    'skip_empty_lines' => false,
                    'trim' => false,
                    'first_row_header' => false,
                ),
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detection' => false,
                    'skip_empty' => true,
                    'trim' => true,
                    'first_row_header' => true,
                ),
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'line_endings' => "\n",
                    'escape' => "\\",
                    'use_bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detection' => false,
                    'skip_empty_lines' => true,
                    'trim' => true,
                    'first_row_header' => true,
                ),
            ),
        );
    }

    /**
     * @dataProvider providerCreateDialect
     */
    public function testCreateDialect($method, $expected)
    {
        $dialect = call_user_func(array('\\CSanquer\\ColibriCsv\\Dialect', $method));
        $this->assertInstanceOf('\\CSanquer\\ColibriCsv\\Dialect', $dialect);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, call_user_func(array($dialect, Inflector::camelize('get_'.$key))), 'the value is not the expected for the option '.$key);
        }

    }

    public function providerCreateDialect()
    {
        return array(
            array(
                'createExcelDialect',
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'CP1252',
                    'line_endings' => "\r\n",
                    'escape' => "\\",
                    'escape_double' => true,
                    'use_bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detection' => false,
                    'skip_empty_lines' => false,
                    'trim' => false,
                    'first_row_header' => false,
                ),
            ),
            array(
                'createUnixDialect',
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'line_endings' => "\n",
                    'escape' => "\\",
                    'escape_double' => true,
                    'use_bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detection' => false,
                    'skip_empty_lines' => false,
                    'trim' => false,
                    'first_row_header' => false,
                ),
            ),
        );
    }

    /**
     * @dataProvider providerGetDefaultOptions
     */
    public function testGetDefaultOptions($CSVType, $expected)
    {
        $this->assertEquals($expected, Dialect::getDialectDefaultOptions($CSVType));
    }

    public function providerGetDefaultOptions()
    {
        return array(
            array(
                'excel',
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'CP1252',
                    'enclosing_mode' => 'minimal',
                    'eol' => "\r\n",
                    'escape' => "\\",
                    'escape_double' => true,
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                    'first_row_header' => false,
                ),
            ),
            array(
                'unix',
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'enclosing_mode' => 'minimal',
                    'eol' => "\n",
                    'escape' => "\\",
                    'escape_double' => true,
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                    'first_row_header' => false,
                ),
            ),
        );
    }

    /**
     * @dataProvider providerGetSetLineEndings
     */
    public function testGetSetLineEndings($input, $expected, $expectedLabel)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setLineEndings($input));
        $this->assertEquals($expected, $this->dialect->getLineEndings());
        $this->assertEquals($expectedLabel, $this->dialect->getLineEndings(true));
        $this->assertEquals($expectedLabel, $this->dialect->getLineEndingsAsLabel(true));
    }

    public function providerGetSetLineEndings()
    {
        return array(
            array(null, "\r\n", 'windows'),
            array('', "\r\n", 'windows'),
            array("\r\n", "\r\n", 'windows'),
            array('windows', "\r\n", 'windows'),
            array('win', "\r\n", 'windows'),
            array('linux', "\n", 'unix'),
            array('unix', "\n", 'unix'),
            array("\n", "\n", 'unix'),
            array('mac', "\r", 'mac'),
            array('macos', "\r", 'mac'),
            array("\r", "\r", 'mac'),
        );
    }

    /**
     * @dataProvider providerGetSetDelimiter
     */
    public function testGetSetDelimiter($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setDelimiter($input));
        $this->assertEquals($expected, $this->dialect->getDelimiter());
    }

    public function providerGetSetDelimiter()
    {
        return array(
            array(null, ';'),
            array('', ';'),
            array(',', ","),
        );
    }

    /**
     * @dataProvider providerGetSetEnclosure
     */
    public function testGetSetEnclosure($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setEnclosure($input));
        $this->assertEquals($expected, $this->dialect->getEnclosure());
    }

    public function providerGetSetEnclosure()
    {
        return array(
            array(null, '"'),
            array('', '"'),
            array('\'', '\''),
        );
    }

    /**
     * @dataProvider providerGetSetEnclosingMode
     */
    public function testGetSetEnclosingMode($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setEnclosingMode($input));
        $this->assertEquals($expected, $this->dialect->getEnclosingMode());
    }

    public function providerGetSetEnclosingMode()
    {
        return array(
            array(null, Dialect::ENCLOSING_MINIMAL),
            array('a', Dialect::ENCLOSING_MINIMAL),
            array(1, Dialect::ENCLOSING_MINIMAL),
            array(Dialect::ENCLOSING_MINIMAL, Dialect::ENCLOSING_MINIMAL),
            array(Dialect::ENCLOSING_ALL, Dialect::ENCLOSING_ALL),
            array(Dialect::ENCLOSING_NONNUMERIC, Dialect::ENCLOSING_NONNUMERIC),
        );
    }

    /**
     * @dataProvider providerGetSetEscapeDouble
     */
    public function testGetSetEscapeDouble($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setEscapeDouble($input));
        $this->assertEquals($expected, $this->dialect->getEscapeDouble());
    }

    public function providerGetSetEscapeDouble()
    {
        return array(
            array(null, false),
            array(0, false),
            array('', false),
            array(false, false),
            array(1, true),
            array(true, true),
        );
    }

    /**
     * @dataProvider providerGetSetEscape
     */
    public function testGetSetEscape($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setEscape($input));
        $this->assertEquals($expected, $this->dialect->getEscape());
    }

    public function providerGetSetEscape()
    {
        return array(
            array(null, "\\"),
            array('', "\\"),
            array('"', '"'),
        );
    }

    /**
     * @dataProvider providerGetSetEncoding
     */
    public function testGetSetEncoding($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setEncoding($input));
        $this->assertEquals($expected, $this->dialect->getEncoding());
    }

    public function providerGetSetEncoding()
    {
        return array(
            array(null, 'CP1252'),
            array('', 'CP1252'),
            array('UTF-8', 'UTF-8'),
        );
    }

    /**
     * @dataProvider providerGetSetTranslit
     */
    public function testGetSetTranslit($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setTranslit($input));
        $this->assertEquals($expected, $this->dialect->getTranslit());
    }

    public function providerGetSetTranslit()
    {
        return array(
            array(null, null),
            array('foobar', null),
            array('translit', 'translit'),
            array('ignore', 'ignore'),
        );
    }

    /**
     * @dataProvider providerGetSetFirstRowHeader
     */
    public function testGetSetFirstRowHeader($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setFirstRowHeader($input));
        $this->assertEquals($expected, $this->dialect->getFirstRowHeader());
    }

    public function providerGetSetFirstRowHeader()
    {
        return array(
            array(null, false),
            array(0, false),
            array('', false),
            array(false, false),
            array(1, true),
            array(true, true),
        );
    }
    
    /**
     * @dataProvider providerGetSetUseBom
     */
    public function testGetSetUseBom($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setUseBom($input));
        $this->assertEquals($expected, $this->dialect->getUseBom());
    }

    public function providerGetSetUseBom()
    {
        return array(
            array(null, false),
            array(0, false),
            array('', false),
            array(false, false),
            array(1, true),
            array(true, true),
        );
    }

    /**
     * @dataProvider providerGetSetTrim
     */
    public function testGetSetTrim($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setTrim($input));
        $this->assertEquals($expected, $this->dialect->getTrim());
    }

    public function providerGetSetTrim()
    {
        return array(
            array(null, false),
            array(0, false),
            array('', false),
            array(false, false),
            array(1, true),
            array(true, true),
        );
    }

    /**
     * @dataProvider providerGetSetForceEncodingDetection
     */
    public function testGetSetForceEncodingDetection($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setForceEncodingDetection($input));
        $this->assertEquals($expected, $this->dialect->getForceEncodingDetection());
    }

    public function providerGetSetForceEncodingDetection()
    {
        return array(
            array(null, false),
            array(false, false),
            array(true, true),
            array(0, false),
            array('0', false),
            array(1, true),
            array('1', true),
        );
    }

    /**
     * @dataProvider providerGetSetSkipEmptyLines
     */
    public function testGetSetSkipEmptyLines($input, $expected)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\Dialect', $this->dialect->setSkipEmptyLines($input));
        $this->assertEquals($expected, $this->dialect->getSkipEmptyLines());
    }

    public function providerGetSetSkipEmptyLines()
    {
        return array(
            array(null, false),
            array(false, false),
            array(true, true),
            array(0, false),
            array('0', false),
            array(1, true),
            array('1', true),
        );
    }
}
