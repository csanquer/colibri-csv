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
    
    /**
     * open a csv file to write
     *
     * @param  string                           $filename default = null
     * @return \CSanquer\ColibriCsv\AbstractCsv
     */
    public function open($filename = null)
    {
        parent::open($filename);
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
     * @param resource $fileHandler
     * @param array    $values
     *
     * @return \CSanquer\ColibriCsv\CsvWriter
     *
     * @throws \InvalidArgumentException
     */
    protected function write($fileHandler, $values)
    {
        $delimiter = $this->dialect->getDelimiter();
        $enclosure = $this->dialect->getEnclosure();
        $eol = $this->dialect->getLineEndings();
        $escape = $this->dialect->getEscape();
        $trim = $this->dialect->getTrim();
        $enclosingMode = $this->dialect->getEnclosingMode();
        $escapeDouble = $this->dialect->getEscapeDouble();
        $line = implode($this->dialect->getDelimiter(), array_map(function($var) use ($delimiter, $enclosure, $eol, $escape, $trim, $enclosingMode, $escapeDouble) {
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

            if (
                $enclosingMode === Dialect::ENCLOSING_ALL ||
                ($enclosingMode === Dialect::ENCLOSING_MINIMAL && preg_match('/['.preg_quote($enclosure.$delimiter.$eol, '/').']+/', $clean)) ||
                ($enclosingMode === Dialect::ENCLOSING_NONNUMERIC && preg_match('/[^\d\.]+/', $clean))
            )
            {
                $var = $enclosure.$clean.$enclosure;
            } else {
                $var = $clean;
            }

            return $var;
        }, $values))
            // Add line ending
            .$this->dialect->getLineEndings();

        // Write to file
        fwrite($fileHandler, $this->convertEncoding($line, 'UTF-8', $this->dialect->getEncoding()));

        return $this;
    }

    /**
     * write a CSV row from a PHP array
     *
     * @param array $values
     *
     * @return \CSanquer\ColibriCsv\CsvWriter
     */
    public function writeRow(array $values)
    {
        if (!$this->isFileOpened()) {
            $this->openFile($this->fileHandlerMode);
        }

        return $this->write($this->getFileHandler(), $values);
    }

    /**
     * write CSV rows from a PHP arrays
     *
     * @param array rows (multiple arrays of values)
     *
     * @return \CSanquer\ColibriCsv\CsvWriter
     */
    public function writeRows(array $rows)
    {
        foreach ($rows as $values) {
            $this->writeRow($values);
        }

        return $this;
    }
}
