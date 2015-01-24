<?php

namespace CSanquer\ColibriCsv;

use CSanquer\ColibriCsv\Utility\Transcoder;

/**
 * Common Abstract Csv
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
abstract class AbstractCsv
{
    const MODE_READING = 'reading';
    const MODE_WRITING = 'writing';
    /**
     *
     * @var Dialect
     */
    protected $dialect;

    /**
     * @var Transcoder
     */
    protected $transcoder;

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
     * CSV Header row
     * 
     * @var array
     */
    protected $headers = array();
    
    /**
     *
     * Default Excel configuration
     *
     * @param Dialect|array $options default = array()
     */
    public function __construct($options = array())
    {
        $this->dialect = $options instanceof Dialect ? $options : new Dialect($options);
        $this->transcoder = new Transcoder($this->dialect->getEncoding());
    }

    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * @return array compatible file handler modes
     */
    abstract protected function getCompatibleFileHanderModes();

    /**
     * get CSV first row header if enabled
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * check if a file handle mode is allowed
     *
     * @param string $mode
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFileHandleMode($mode)
    {
        if (!in_array($mode, $this->getCompatibleFileHanderModes())) {
            throw new \InvalidArgumentException(
                'The file handler mode "'.$mode.'" is not valid. Allowed modes : "'.
                implode('", "', $this->getCompatibleFileHanderModes()).'".'
            );
        }
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
     * @param  string|resource $filename filename or stream resource
     * @return AbstractCsv
     */
    public function setFilename($filename)
    {
        return $this->setFile($filename);
    }

    /**
     *
     * @param  string|resource $file filename or stream resource
     * @return AbstractCsv
     */
    public function setFile($file)
    {
        if (is_resource($file)) {
            if (get_resource_type($file) !== 'stream') {
                throw new \InvalidArgumentException('The file resource must be valid stream resource.');
            }

            $streamMeta = stream_get_meta_data($file);
            $mode = $streamMeta['mode'];
            $this->checkFileHandleMode($mode);
            $this->fileHandler = $file;
            $this->fileHandlerMode = $mode;
            $file = $streamMeta['uri'];
        } else {
            if ($this->mode == self::MODE_READING && !file_exists($file)) {
                throw new \InvalidArgumentException('The file "'.$file.'" does not exists.');
            }

            if ($this->isFileOpened() && $file != $this->filename) {
                $this->closeFile();
            }
        }

        $this->filename = $file;

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
     * @return AbstractCsv
     */
    protected function writeBom()
    {
        if ($this->dialect->getUseBom()) {
            $bom = $this->transcoder->getBOM($this->dialect->getEncoding());
            // Write the unicode BOM code
            if (!empty($bom) && $this->isFileOpened()) {
                fwrite($this->fileHandler, is_array($bom) ? $bom[0] : $bom);
            }
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
        return $str !== false && $this->dialect->getUseBom() ? str_replace($this->transcoder->getBOM($this->dialect->getEncoding()), '', $str) : $str;
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
        return $str !== false ? $this->transcoder->transcode($str, $from, $to, $this->dialect->getTranslit()) : $str;
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
        if (!$this->isFileOpened()) {
            $mode = empty($mode) ? 'rb' : $mode;
            $this->fileHandler = @fopen($this->filename, $mode);
            if (!$this->isFileOpened()) {
                $modeLabel = $this instanceof CsvReader ? self::MODE_READING : self::MODE_WRITING;
                throw new \InvalidArgumentException('Could not open file "'.$this->filename.'" for '.$modeLabel.'.');
            }
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
     * @param  string|resource $file filename or stream resource, default = null
     * @return AbstractCsv
     *
     * @throws \InvalidArgumentException
     */
    public function open($file = null)
    {
        if (!is_null($file)) {
            $this->setFile($file);
        }
        $this->openFile($this->fileHandlerMode);

        return $this;
    }

    /**
     * Open a temp php stream for reading from a CSV string or Writing CSV to a PHP string
     *
     * @param  string                           $csvContent
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    public function createTempStream($csvContent = null)
    {
        $this->closeFile();

        $stream = fopen('php://temp', $this->mode == self::MODE_WRITING ? 'wb' : 'r+b');
        if ($this->mode == self::MODE_READING && $csvContent !== null && $csvContent !== '') {
            fwrite($stream, $csvContent);
            rewind($stream);
        }

        $this->open($stream);

        return $this;
    }

    /**
     * get the current stream resource (or file) content
     *
     * @return string
     */
    public function getFileContent()
    {
        $this->openFile();
        $content = '';

        if ($this->isFileOpened()) {
            $current = ftell($this->fileHandler);
            rewind($this->fileHandler);
            $content = stream_get_contents($this->fileHandler);
            fseek($this->fileHandler, $current);
        }

        return $content;
    }

    /**
     * close the current csv file
     *
     * @return AbstractCsv
     */
    public function close()
    {
        $this->closeFile();

        return $this;
    }
}
