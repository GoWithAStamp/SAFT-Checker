<?php

namespace App\Services\SaftValidator\Parsers;

class SaftParser
{
    public static function parse(string $filePath): \SimpleXMLElement
    {
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            throw new \RuntimeException('Não foi possível carregar o ficheiro SAFT-PT.');
        }

        $namespaces = $xml->getNamespaces(true);
        if (!empty($namespaces)) {
            $xml->registerXPathNamespace('saft', reset($namespaces));
        }

        return $xml;
    }
}
