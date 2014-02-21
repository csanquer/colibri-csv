<?php

namespace CSanquer\ColibriCsv\Tests\Csv;

use CSanquer\ColibriCsv\AbstractCsv;
use CSanquer\ColibriCsv\Dialect;
use CSanquer\ColibriCsv\Tests\AbstractCsvTestCase;

/**
 * AbstractCsvTest
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class AbstractCsvTest extends AbstractCsvTestCase
{
    /**
     *
     * @var AbstractCsv
     */
    protected $structure;

    protected function setUp()
    {
        $this->structure = $this->getMockForAbstractClass('CSanquer\ColibriCsv\AbstractCsv');
    }

    /**
     * @dataProvider providerGetSetDialect
     */
    public function testGetSetDialect($input)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->setDialect($input));
        $this->assertInstanceOf('\\CSanquer\\ColibriCsv\\Dialect', $this->structure->getDialect());
    }

    public function providerGetSetDialect()
    {
        return array(
            array(new Dialect()),
        );
    }

    /**
     * @dataProvider providerGetSetFile
     */
    public function testGetSetFile($input, $expected, $expectedResource = false)
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->setFile($input));
        $this->assertEquals($expected, $this->structure->getFilename());
        if ($expectedResource) {
            $this->assertInternalType('resource', $this->structure->getFileHandler());
        }
    }

    public function providerGetSetFile()
    {
        return array(
            array(null, null),
            array('', ''),
            array(__DIR__.'/../Fixtures/test1.csv', __DIR__.'/../Fixtures/test1.csv', false),
        );
    }
    
    public function testGetSetFilename()
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->setFilename(__DIR__.'/../Fixtures/test1.csv'));
        $this->assertEquals(__DIR__.'/../Fixtures/test1.csv', $this->structure->getFilename());
    }

    public function testOpen()
    {
        $this->assertFalse($this->structure->isFileOpened());
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->open(__DIR__.'/../Fixtures/test1.csv'));
        $this->assertTrue($this->structure->isFileOpened());
        $this->assertInternalType('resource', $this->getFileHandlerValue($this->structure));

        return $this->structure;
    }
    
    public function testOpenNewFile()
    {
        $file1 = __DIR__.'/../Fixtures/test1.csv';
        $file2 = __DIR__.'/../Fixtures/test2.csv';

        $this->assertFalse($this->structure->isFileOpened());
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->open($file1));
        $this->assertEquals($file1, $this->structure->getFilename());
        $this->assertTrue($this->structure->isFileOpened());
        $fileHandler1 = $this->getFileHandlerValue($this->structure);
        $this->assertInternalType('resource', $fileHandler1);

        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->open($file2));
        $this->assertEquals($file2, $this->structure->getFilename());
        $this->assertTrue($this->structure->isFileOpened());
        $this->assertNotInternalType('resource', $fileHandler1);
        $fileHandler2 = $this->getFileHandlerValue($this->structure);
        $this->assertInternalType('resource', $fileHandler2);

        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->close());
        $this->assertFalse($this->structure->isFileOpened());
        $this->assertNotInternalType('resource', $fileHandler2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOpenNoFilename()
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->open());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOpenNoExistingFile()
    {
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $this->structure->open(__DIR__.'/../Fixtures/abc.csv'));
    }

    /**
     * @depends testOpen
     */
    public function testClose($structure)
    {
        $this->assertTrue($structure->isFileOpened());
        $this->assertInstanceOf('CSanquer\ColibriCsv\AbstractCsv', $structure->close());
        $this->assertFalse($structure->isFileOpened());
        $this->assertNotInternalType('resource', $this->getFileHandlerValue($structure));
    }
}
