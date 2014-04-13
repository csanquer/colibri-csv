<?php

namespace CSanquer\ColibriCsv;

/**
 * Csv Writer
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class CsvWriter extends AbstractCsv
{
    /**
     *
     * Default Excel Writing configuration
     *
     * available options :
     * - delimiter : (default = ';')
     * - enclosure : (default = '"')
     * - encoding : (default = 'CP1252')
     * - eol : (default = "\r\n")
     * - escape : (default = "\\")
     * - bom : (default = false)  add UTF8 BOM marker
     * - translit : (default = 'translit')  iconv translit option possible values : 'translit', 'ignore', null
     * - trim : (default = false) trim each values on each line
     *
     * N.B. : Be careful, the option 'trim' decrease significantly the performances
     *
     * @param array $options Dialect Options to describe CSV file parameters
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->mode = self::MODE_WRITING;
        $this->fileHandlerMode = 'wb';
    }

    protected function getCompatibleFileHanderModes()
    {
        return array('wb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b');
    }

    /**
     * open a csv file to write
     *
     * @param  string|resource $file filename or stream resource, default = null
     * @return AbstractCsv
     */
    public function open($file = null)
    {
        parent::open($file);
        $this->writeBom();

        return $this;
    }

    /**
     * get HTTP headers for streaming CSV file
     *
     * @param  string $filename
     * @return array
     */
    public function getHttpHeaders($filename)
    {
        return array(
            'Content-Type' => 'application/csv',
            'Content-Disposition' => 'attachment;filename="'.$filename.'"',
        );
    }

    /**
     * echo HTTP headers for streaming CSV file
     *
     * @param string $filename
     *
     * @codeCoverageIgnore this cannot be tested correctly by PHPUnit because it send HTTP headers
     */
    public function createHttpHeaders($filename)
    {
        $headers = $this->getHttpHeaders($filename);
        foreach ($headers as $key => $value) {
            header($key.': '.$value);
        }
    }

    /**
     *
     * @param resource|null $fileHandler
     * @param array         $values
     *
     * @return CsvWriter
     *
     * @throws \InvalidArgumentException
     */
    protected function write($fileHandler, $values)
    {
        if ($this->isFileOpened()) {
            $delimiter = $this->dialect->getDelimiter();
            $enclosure = $this->dialect->getEnclosure();
            $eol = $this->dialect->getLineEndings();
            $escape = $this->dialect->getEscape();
            $trim = $this->dialect->getTrim();
            $enclosingMode = $this->dialect->getEnclosingMode();
            $escapeDouble = $this->dialect->getEscapeDouble();
            $line = implode($this->dialect->getDelimiter(), array_map(
                function ($var) use ($delimiter, $enclosure, $eol, $escape, $trim, $enclosingMode, $escapeDouble) {
                    // Escape enclosures and enclosed string
                    if ($escapeDouble) {
                        // double enclosure
                        $searches = array($enclosure);
                        $replacements = array($enclosure.$enclosure);
                    } else {
                        // use escape character
                        $searches = array($enclosure);
                        $replacements = array($escape.$enclosure);
                    }
                    $clean = str_replace($searches, $replacements, $trim ? trim($var) : $var);

                    if ($enclosingMode === Dialect::ENCLOSING_ALL ||
                        (
                            $enclosingMode === Dialect::ENCLOSING_MINIMAL &&
                            preg_match('/['.preg_quote($enclosure.$delimiter.$eol, '/').']+/', $clean)
                        ) ||
                        ($enclosingMode === Dialect::ENCLOSING_NONNUMERIC && preg_match('/[^\d\.]+/', $clean))
                    ) {
                        $var = $enclosure.$clean.$enclosure;
                    } else {
                        $var = $clean;
                    }

                    return $var;
                },
                $values
            ))
            // Add line ending
            .$this->dialect->getLineEndings();

            // Write to file
            fwrite($fileHandler, $this->convertEncoding($line, 'UTF-8', $this->dialect->getEncoding()));
        }

        return $this;
    }

    /**
     * write a CSV row from a PHP array
     *
     * @param array $values
     *
     * @return CsvWriter
     */
    public function writeRow(array $values)
    {
        $this->openFile($this->fileHandlerMode);

        return $this->write($this->getFileHandler(), $values);
    }

    /**
     * write CSV rows from a PHP arrays
     *
     * @param array rows (multiple arrays of values)
     *
     * @return CsvWriter
     */
    public function writeRows(array $rows)
    {
        foreach ($rows as $values) {
            $this->writeRow($values);
        }

        return $this;
    }
}
