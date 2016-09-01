CSanquer ColibriCSV
===================

**LOOKING FOR NEW MAINTAINER**

[![Latest Stable Version](https://poser.pugx.org/csanquer/colibri-csv/v/stable.png)](https://packagist.org/packages/csanquer/colibri-csv)
[![Latest Unstable Version](https://poser.pugx.org/csanquer/colibri-csv/v/unstable.png)](https://packagist.org/packages/csanquer/colibri-csv)
[![Build Status](https://travis-ci.org/csanquer/colibri-csv.png?branch=master)](https://travis-ci.org/csanquer/colibri-csv)
[![Code Coverage](https://scrutinizer-ci.com/g/csanquer/colibri-csv/badges/coverage.png?s=b11d084db20368214c00aad6d2a434e4530c5913)](https://scrutinizer-ci.com/g/csanquer/colibri-csv/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/csanquer/colibri-csv/badges/quality-score.png?s=909ff1ccaafc6294e4a250c71d8e85b113b4638f)](https://scrutinizer-ci.com/g/csanquer/colibri-csv/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7feeab74-76c0-4404-b9d8-009d07c0f652/mini.png)](https://insight.sensiolabs.com/projects/7feeab74-76c0-4404-b9d8-009d07c0f652)

[![Dependency Status](https://www.versioneye.com/user/projects/52f4e68dec1375dc7b00014d/badge.png)](https://www.versioneye.com/user/projects/52f4e68dec1375dc7b00014d)
[![License](https://poser.pugx.org/csanquer/colibri-csv/license.png)](https://packagist.org/packages/csanquer/colibri-csv)
[![Daily Downloads](https://poser.pugx.org/csanquer/colibri-csv/d/daily.png)](https://packagist.org/packages/csanquer/colibri-csv)
[![Monthly Downloads](https://poser.pugx.org/csanquer/colibri-csv/d/monthly.png)](https://packagist.org/packages/csanquer/colibri-csv)
[![Total Downloads](https://poser.pugx.org/csanquer/colibri-csv/downloads.png)](https://packagist.org/packages/csanquer/colibri-csv)

A lightweight, simple and performant CSV Reader/Writer PHP 5.4+ Library, inspired from Python CSV Module.
Fully Tested, very memory efficient and able to parse/write CSV files that weigh over 100 Mb.

This is a fork of [Spyrit LightCSV library](https://github.com/spyrit/LightCsv), I have developed previously in this company.

Installation
------------

* get composer http://getcomposer.org/ and install dependencies

```bash
curl -s https://getcomposer.org/installer | php
```

* add "[https://packagist.org/packages/csanquer/colibri-csv](csanquer/colibri-csv)" package to your composer.json file require section

```bash
php composer.phar require csanquer/colibri-csv:1.0.*
```

* install dependencies

```bash
php composer.phar install
```
* include vendor/autoload.php

How To
------

###Read

Instanciate a new CSVReader with the following CSV parameters:

* field delimiter (default for Excel = ; )
* field enclosure character  (default for Excel = " ) 
* character encoding = (default for Excel = CP1252 )
* end of line character (default for Excel = "\r\n" )
* escape character (default for Excel = "\\" )
* first_row_header : (default for excel = false) use the first CSV row as header
* UTF8 BOM (default false) force removing BOM
* transliteration (default for Excel = null ) available options : 'translit', 'ignore', null
* force encoding detection (default for Excel = false )
* skip empty lines (default for Excel = false ) lines which all values are empty
* trim (default = false for Excel) trim all values


```php
use CSanquer\ColibriCsv\CsvReader;

// create the reader
$reader = new CsvReader(array(
    'delimiter' => ';', 
    'enclosure' => '"', 
    'encoding' => 'CP1252', 
    'eol' => "\r\n", 
    'escape' => "\\", 
    'first_row_header' => false,
    'bom' => false, 
    'translit' => 'translit',
    'force_encoding_detect' => false,
    'skip_empty' => false,
    'trim' => false,
));

//Open the csv file to read
$reader->open('test.csv');

// or open an existing stream resource
$stream = fopen('test.csv', 'rb');
$reader->open($stream);

// or read an existing CSV string by creating a temporary in-memory file stream (not recommended for large CSV)
$reader->createTempStream('lastname,firstname,age
Martin,Durand,"28"
Alain,Richard,"36"
');

//Read each row
foreach ($reader as $row) {
    // do what you want with the current row array : $row
}

// or get all rows in one call (not recommended for large CSV)
$csvRows = $reader->getRows();

//close the csv file stream
$reader->close();
```

###Write

Instanciate a new CSVWriter with the following CSV parameters:

* field delimiter (default for Excel = ; )
* field enclosure character  (default for Excel = " ) 
* character encoding = (default for Excel = CP1252 ) 
* end of line character (default for Excel = "\r\n" )
* escape character (default for Excel = "\\" )
* first_row_header : (default for excel = false) use the PHP keys as CSV headers and write a first row with them
* enclosing_mode (default = 'minimal'), possible values :
  * all : always enclose string
  * minimal : enclose string only if the delimiter, enclosure or line ending character is present
  * nonumeric : enclose string only if the value is non numeric (other character than digits and dot)
* escape_double (default = true) if true double the enclosure to escape it, else escape it with escape character
* UTF8 BOM (default false) force writing BOM if encoding is UTF-8
* transliteration (default for Excel = null ) available options : 'translit', 'ignore', null
* trim (default = false for Excel) trim all values

```php
use CSanquer\ColibriCsv\CsvWriter;

// create the writer
$writer = new CsvWriter(array(
    'delimiter' => ';', 
    'enclosure' => '"', 
    'encoding' => 'CP1252', 
    'enclosing_mode' => 'minimal',
    'escape_double' => true,
    'eol' => "\r\n", 
    'escape' => "\\", 
    'bom' => false, 
    'translit' => 'translit',
    'first_row_header' => false,
    'trim' => false,
));

//Open the csv file to write
$writer->open('test.csv');

// or open an existing stream resource
$stream = fopen('test.csv', 'wb');
$writer->open($stream);

// or create an empty temporary in-memory file stream to write in and get CSV text later 
// (not recommended for large CSV file)
$writer->createTempStream();

//Write a row
$writer->writeRow(array('a', 'b', 'c'));

//Write multiple rows at the same time
$writer->writeRows(array(
    array('d', 'e', 'f'),
    array('g', 'h', 'i'),
    array('j', 'k', 'l'),
));

// get the CSV Text as plain string
$writer->getFileContent();

//close the csv file
$writer->close();
```

### Configuration : Dialect class

Instead of giving directly an array to the CsvReader or CsvWriter constructor, you can create a Dialect object, use setter methods to change parameters and pass it to the CsvReader (or CsvWriter) :

*Be careful, the options 'force_encoding_detect', 'skip_empty' and 'trim' decrease significantly the performances*

```php
use CSanquer\ColibriCsv\Dialect;
use CSanquer\ColibriCsv\CsvReader;
use CSanquer\ColibriCsv\CsvWriter;

// create a dialect with some CSV parameters
$dialect = new Dialect(array(
    'delimiter' => ';', 
    'enclosure' => '"', 
    'enclosing_mode' => 'minimal',
    'encoding' => 'CP1252', 
    'eol' => "\r\n", 
    'escape' => "\\", 
    'escape_double' => true,
    'bom' => false, 
    'translit' => 'translit',
    'force_encoding_detect' => false,
    'skip_empty' => false,
    'trim' => false,
);

// change a parameter
$dialect->setLineEndings("\n");

// create the reader
$reader = new CsvReader($dialect);

//or a writer
$writer = new CsvWriter($dialect);

```

Requirements
------------

* PHP >= 5.4
* extension mbstring

Suggested :

* extension iconv

Tests
-----

run unit tests with phpunit :

```sh
phpunit
```

run benchmark test :

```sh
php tests/benchmark_test.php
```

Licensing
---------

This library is a Fork of Spyrit LightCSV

License LGPL 3

* Copyright (C) 2012-2013 Spyrit Systeme (Spyrit LightCSV)
* Copyright (C) 2014 Charles Sanquer

This file is part of ColibriCSV.

ColibriCSV is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

ColibriCSV is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with ColibriCSV.  If not, see <http://www.gnu.org/licenses/>.
