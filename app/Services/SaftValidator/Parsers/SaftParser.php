<?php

namespace App\Services\SaftValidator\Parsers;

/**
 * XML parser for SAFT-PT files with automatic namespace handling.
 *
 * SAFT-PT files may or may not declare an XML namespace. This parser
 * detects the namespace and registers it under the "saft" prefix so
 * that XPath queries work consistently regardless of whether the file
 * uses a default namespace.
 */
class SaftParser
{
    /**
     * Parse a SAFT-PT XML file into a SimpleXMLElement.
     *
     * @param string $filePath Absolute path to the XML file.
     * @return \SimpleXMLElement Parsed XML tree with namespace prefix registered.
     * @throws \RuntimeException If the file cannot be loaded.
     */
    public static function parse(string $filePath): \SimpleXMLElement
    {
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            throw new \RuntimeException('Não foi possível carregar o ficheiro SAFT-PT.');
        }

        // Register the default namespace under "saft:" so XPath queries work
        $namespaces = $xml->getNamespaces(true);
        if (!empty($namespaces)) {
            $xml->registerXPathNamespace('saft', reset($namespaces));
        }

        return $xml;
    }
}
