<?php

namespace CSanquer\ColibriCsv;

use CSanquer\ColibriCsv\Utility\Converter;

/**
 * Csv Reader
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class CsvReader extends AbstractCsv implements \Iterator, \Countable
{
    /**
     *
     * @var int
     */
    private $position = 0;

    /**
     *
     * @var array
     */
    private $currentValues = array();

    /**
     *
     * @var string
     */
    protected $detectedEncoding;

    /**
     *
     * Default Excel Reading configuration
     *
     * available options :
     * - delimiter : (default = ';')
     * - enclosure : (default = '"')
     * - encoding : (default = 'CP1252')
     * - eol : (default = "\r\n")
     * - escape : (default = "\\")
     * - bom : (default = false)  add UTF8 BOM marker
     * - translit : (default = 'translit')  iconv translit option possible values : 'translit', 'ignore', null
     * - force_encoding_detect : (default = false)
     * - skip_empty : (default = false)  remove lines with empty values
     * - trim : (default = false) trim each values on each line
     *
     * N.B. : Be careful, the options 'force_encoding_detect', 'skip_empty' and 'trim'
     * decrease significantly the performances
     *
     * @param array $options Dialect Options to describe CSV file parameters
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->mode = self::MODE_READING;
        $this->fileHandlerMode = 'rb';
    }

    protected function getCompatibleFileHanderModes()
    {
        return array('rb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b');
    }

    /**
     * open a csv file to read
     *
     * @param  string|resource $file filename or stream resource, default = null
     * @return CsvReader
     */
    public function open($file = null)
    {
        parent::open($file);
        $this->detectEncoding();

        return $this;
    }

    /**
     * Detect current file encoding if ForceEncodingDetection is set to true or encoding parameter is null
     */
    protected function detectEncoding()
    {
        $this->detectedEncoding = $this->dialect->getEncoding();
        if ($this->isFileOpened() && ($this->dialect->getForceEncodingDetection() || empty($this->detectedEncoding))) {
            //only read the 100 first lines to detect encoding to improve performance
            $text = '';
            $line = 0;
            while (!feof($this->getFileHandler()) && $line <= 100) {
                $text .= fgets($this->getFileHandler());
                $line++;
            }

            if ($text !== false) {
                $this->detectedEncoding = Converter::detectEncoding($text, $this->dialect->getEncoding());
            }
        }
    }

    /**
     *
     * @param  resource|null $fileHandler
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function readLine($fileHandler)
    {
        $row = null;
        if (!is_resource($fileHandler)) {
            throw new \InvalidArgumentException('A valid file handler resource must be passed as parameter.');
        }

        if (!feof($fileHandler)) {
            $enclosure = $this->dialect->getEnclosure();
            $escape = $this->dialect->getEscape();
            $line = fgetcsv($fileHandler, null, $this->dialect->getDelimiter(), $enclosure, $escape);

            if ($line !== false) {
                $trim = $this->dialect->getTrim();
                $translit = $this->dialect->getTranslit();
                $detectedEncoding = $this->detectedEncoding;

                if ($this->position <= 0) {
                    $line[0] = $this->removeBom($line[0]);
                }

                $row = array_map(function ($var) use ($enclosure, $escape, $trim, $translit, $detectedEncoding) {
                    // workaround when escape char is not equals to double quote
                    if ($enclosure === '"' && $escape !== $enclosure) {
                        $var = str_replace($escape.$enclosure, $enclosure, $var);
                    }

                    $var = Converter::convertEncoding($var, $detectedEncoding, 'UTF-8', $translit);

                    return $trim ? trim($var) : $var;
                }, $line);

                $notEmptyCount = count(array_filter($row, function ($var) {
                    return $var !== false && $var !== null && $var !== '';
                }));

                if ($this->dialect->getSkipEmptyLines() && 0 === $notEmptyCount) {
                    $row = false;
                }
            }
        }

        return $row;
    }

    /**
     * return the current row and go to the next row
     *
     * @return array|false
     */
    public function getRow()
    {
        if ($this->valid()) {
            $current = $this->current();
            $this->next();

            return $current;
        } else {
            return false;
        }
    }

    /**
     * get All rows as an array
     *
     * N.B.: Be careful, this method can consume a lot of memories on large CSV files.
     *
     * You should prefer iterate over the reader instead.
     *
     * @return array all rows in the CSV files
     */
    public function getRows()
    {
        $rows = array();
        $this->rewind();

        while ($this->valid()) {
            $rows[] = $this->current();
            $this->next();
        }

        return $rows;
    }

    /**
     * reset CSV reading to 1st line
     *
     * aliases for iterator rewind
     */
    public function reset()
    {
        $this->rewind();
    }

    /**
     *
     * @return array
     */
    public function current()
    {
        return $this->currentValues;
    }

    /**
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->currentValues = $this->readLine($this->getFileHandler());
        $this->position++;

        if ($this->dialect->getSkipEmptyLines() && $this->currentValues === false) {
            $this->next();
        }

        return $this->currentValues;
    }

    public function rewind()
    {
        $this->openFile($this->fileHandlerMode);
        if ($this->isFileOpened()) {
            rewind($this->getFileHandler());

            $this->position = -1;
            $this->next();
        }
    }

    /**
     *
     * @return bool
     */
    public function valid()
    {
        return $this->currentValues !== null;
    }

    public function count()
    {
        $count = 0;
        $this->openFile($this->fileHandlerMode);
        if ($this->isFileOpened()) {
            rewind($this->getFileHandler());

            $enclosure = $this->dialect->getEnclosure();
            $escape = $this->dialect->getEscape();
            $delimiter = $this->dialect->getDelimiter();

            if ($this->dialect->getSkipEmptyLines()) {
                while (!feof($this->getFileHandler())) {
                    $line = fgetcsv($this->getFileHandler(), null, $delimiter, $enclosure, $escape);
                    if (!empty($line)) {
                        $notEmptyCount = count(array_filter($line, function ($var) {
                            // empty row pattern without alphanumeric
                            return $var !== false && $var !== null && $var !== '' && preg_match('([[:alnum:]]+)', $var);
                        }));
                        if (0 !== $notEmptyCount) {
                            $count++;
                        }
                    }
                }
            } else {
                while (!feof($this->getFileHandler())) {
                    $line = fgetcsv($this->getFileHandler(), null, $delimiter, $enclosure, $escape);
                    if (!empty($line)) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
