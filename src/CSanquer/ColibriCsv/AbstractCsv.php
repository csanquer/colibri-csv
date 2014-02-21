<?php

namespace CSanquer\ColibriCsv;

use CSanquer\ColibriCsv\Utility\Converter;

/**
 * Common Abstract Csv
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
abstract class AbstractCsv
{
    CONST MODE_READING = 'reading';
    CONST MODE_WRITING = 'writing';
    
    /**
     *
     * @var Dialect
     */
    protected $dialect;

    /**
     *
     * @var string
     */
    protected $filename;

    /**
     *
     * @var string
     */
    protected $fileHandlerMode;

    /**
     *
     * @var string
     */
    protected $mode;
    
    /**
     *
     * @var resource
     */
    protected $fileHandler;

    /**
     *
     * Default Excel configuration
     *
     * @param Dialect|array $options default = array()
     */
    public function __construct($options = array())
    {
        $this->dialect = $options instanceof Dialect ? $options : new Dialect($options);
    }

    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     *
     * @return Dialect
     */
    public function getDialect()
    {
        return $this->dialect;
    }

    public function setDialect(Dialect $dialect)
    {
        $this->dialect = $dialect;

        return $this;
    }
    
    /**
     * 
     * @deprecated since version 1.0.2 use setFile instead
     * 
     * @param string $filename
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    public function setFilename($filename)
    {
        return $this->setFile($filename);
    }
    
    /**
     *
     * @param  string                           $filename
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    public function setFile($filename)
    {
        if ($this->mode == self::MODE_READING && !file_exists($filename)) {
            throw new \InvalidArgumentException('The file "'.$filename.'" does not exists.');
        }
        
        if ($this->isFileOpened() && $filename != $this->filename) {
            $this->closeFile();
        }

        $this->filename = $filename;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     *
     * @return resource stream
     */
    public function getFileHandler()
    {
        return $this->fileHandler;
    }
    
    /**
     * Write UTF-8 BOM code if encoding is UTF-8 and useBom is set to true
     *
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    protected function writeBom()
    {
        if ($this->dialect->getUseBom() && $this->dialect->getEncoding() == 'UTF-8') {
            // Write the UTF-8 BOM code
            fwrite($this->fileHandler, "\xEF\xBB\xBF");
        }

        return $this;
    }

    /**
     * Remove BOM in the provided string
     *
     * @param  string $str
     * @return string
     */
    protected function removeBom($str)
    {
        return $str !== false && $this->dialect->getUseBom() ? str_replace("\xEF\xBB\xBF",'',$str) : $str;
    }

    /**
     *
     * @param  string $str
     * @param  string $from
     * @param  string $to
     * @return string
     */
    protected function convertEncoding($str, $from, $to)
    {
        return $str !== false ? Converter::convertEncoding($str, $from, $to, $this->dialect->getTranslit()) : $str;
    }

    /**
     *
     * @param  string   $mode file handler open mode, default = rb
     * @return resource file handler
     *
     * @throws \InvalidArgumentException
     */
    protected function openFile($mode = 'rb')
    {
        $mode = empty($mode) ? 'rb' : $mode;

        $this->fileHandler = @fopen($this->filename, $mode);
        if ($this->fileHandler === false) {
            $modeLabel = $this instanceof CsvReader ? self::MODE_READING : self::MODE_WRITING;
            throw new \InvalidArgumentException('Could not open file "'.$this->filename.'" for '.$modeLabel.'.');
        }

        return $this->fileHandler;
    }

    /**
     *
     * @return boolean
     */
    protected function closeFile()
    {
        if ($this->isFileOpened()) {
            $ret = @fclose($this->fileHandler);
            $this->fileHandler = null;

            return $ret;
        }

        return false;
    }

    /**
     *
     * check if a file is already opened and is a stream
     *
     * @return boolean
     */
    public function isFileOpened()
    {
        return is_resource($this->fileHandler) && get_resource_type($this->fileHandler) == 'stream';
    }

    /**
     * open a csv file to read or write
     *
     * @param  string                           $filename default = null
     * @return \CSanquer\ColibriCsv\AbstractCsv
     *
     * @throws \InvalidArgumentException
     */
    public function open($filename = null)
    {
        $this->setFilename($filename);
        $this->openFile($this->fileHandlerMode);

        return $this;
    }

    /**
     * close the current csv file
     *
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    public function close()
    {
        $this->closeFile();

        return $this;
    }
}
