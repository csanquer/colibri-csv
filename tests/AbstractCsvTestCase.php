<?php

namespace CSanquer\ColibriCsv\Tests;

use CSanquer\ColibriCsv\AbstractCsv;
use PHPUnit\Framework\TestCase;

/**
 * AbstractCsvTestCase
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
abstract class AbstractCsvTestCase extends TestCase
{
    /**
     * get fileHandler mode (non public access) value for unit tests
     *
     * @param CSanquer\ColibriCsv\AbstractCsv
     *
     * @return mixed
     */
    protected function getFileHandlerModeValue($structure)
    {
        $reflection = new \ReflectionClass($structure);
        $prop = $reflection->getProperty('fileHandlerMode');
        $prop->setAccessible(true);

        return $prop->getValue($structure);
    }
}
