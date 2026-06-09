<?php

namespace App\Services\SaftValidator;

class SaftExportService
{
    public static function toCsv(array $data, array $headers): string
    {
        $output = fopen('php://temp', 'r+');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, array_values($headers), ';', '"', "\\");

        foreach ($data as $row) {
            $csvRow = [];
            foreach (array_keys($headers) as $key) {
                $value = $row[$key] ?? '';
                if (is_array($value)) {
                    $value = count($value) . ' linhas';
                }
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow, ';', '"', "\\");
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public static function toXml(array $data, string $rootElement, string $itemElement, array $headers): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement($rootElement);
        $dom->appendChild($root);

        foreach ($data as $row) {
            $item = $dom->createElement($itemElement);

            foreach (array_keys($headers) as $key) {
                $value = $row[$key] ?? '';
                if (is_array($value)) {
                    $subContainer = $dom->createElement($key);
                    foreach ($value as $subRow) {
                        $subItem = $dom->createElement('Item');
                        foreach ($subRow as $subKey => $subValue) {
                            $subElement = $dom->createElement($subKey);
                            $subElement->appendChild($dom->createTextNode((string) $subValue));
                            $subItem->appendChild($subElement);
                        }
                        $subContainer->appendChild($subItem);
                    }
                    $item->appendChild($subContainer);
                } else {
                    $element = $dom->createElement($key);
                    $element->appendChild($dom->createTextNode((string) $value));
                    $item->appendChild($element);
                }
            }

            $root->appendChild($item);
        }

        return $dom->saveXML();
    }

    public static function headerToCsv(array $header): string
    {
        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Campo', 'Valor'], ';', '"', "\\");
        foreach ($header as $key => $value) {
            if (!empty($value)) {
                fputcsv($output, [$key, $value], ';', '"', "\\");
            }
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return $content;
    }

    public static function headerToXml(array $header): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('Header');
        $dom->appendChild($root);

        foreach ($header as $key => $value) {
            if (!empty($value)) {
                $element = $dom->createElement($key);
                $element->appendChild($dom->createTextNode((string) $value));
                $root->appendChild($element);
            }
        }

        return $dom->saveXML();
    }

    public static function getHeaders(string $section): array
    {
        return match ($section) {
            'customers' => [
                'CustomerID' => 'ID',
                'CustomerTaxID' => 'NIF',
                'CompanyName' => 'Nome',
                'Contact' => 'Contacto',
                'Telephone' => 'Telefone',
                'Email' => 'Email',
                'AddressDetail' => 'Morada',
                'City' => 'Cidade',
                'PostalCode' => 'Código Postal',
                'Country' => 'País',
                'SelfBillingIndicator' => 'Autofaturação',
            ],
            'suppliers' => [
                'SupplierID' => 'ID',
                'SupplierTaxID' => 'NIF',
                'CompanyName' => 'Nome',
                'Contact' => 'Contacto',
                'Telephone' => 'Telefone',
                'Email' => 'Email',
                'AddressDetail' => 'Morada',
                'City' => 'Cidade',
                'PostalCode' => 'Código Postal',
                'Country' => 'País',
            ],
            'products' => [
                'ProductType' => 'Tipo',
                'ProductCode' => 'Código',
                'ProductGroup' => 'Grupo',
                'ProductDescription' => 'Descrição',
                'ProductNumberCode' => 'Código Barras',
            ],
            'tax_table' => [
                'TaxType' => 'Tipo',
                'TaxCountryRegion' => 'Região',
                'TaxCode' => 'Código',
                'Description' => 'Descrição',
                'TaxPercentage' => 'Taxa (%)',
                'TaxAmount' => 'Montante',
                'TaxExpirationDate' => 'Validade',
            ],
            'invoices' => [
                'InvoiceNo' => 'Nº Fatura',
                'ATCUD' => 'ATCUD',
                'InvoiceType' => 'Tipo',
                'InvoiceStatus' => 'Estado',
                'InvoiceDate' => 'Data',
                'SystemEntryDate' => 'Data Sistema',
                'SourceBilling' => 'Origem',
                'CustomerID' => 'Cliente',
                'NetTotal' => 'Base',
                'TaxPayable' => 'IVA',
                'GrossTotal' => 'Total',
            ],
            'payments' => [
                'PaymentRefNo' => 'Referência',
                'ATCUD' => 'ATCUD',
                'PaymentType' => 'Tipo',
                'PaymentStatus' => 'Estado',
                'TransactionDate' => 'Data',
                'SystemEntryDate' => 'Data Sistema',
                'CustomerID' => 'Cliente',
                'NetTotal' => 'Base',
                'TaxPayable' => 'IVA',
                'GrossTotal' => 'Total',
            ],
            'movements' => [
                'DocumentNumber' => 'Documento',
                'ATCUD' => 'ATCUD',
                'MovementType' => 'Tipo',
                'MovementStatus' => 'Estado',
                'MovementDate' => 'Data',
                'SystemEntryDate' => 'Data Sistema',
                'CustomerID' => 'Cliente',
                'ShipFrom' => 'Origem',
                'ShipTo' => 'Destino',
                'MovementStartTime' => 'Hora Início',
            ],
            'working_documents' => [
                'DocumentNumber' => 'Documento',
                'ATCUD' => 'ATCUD',
                'WorkType' => 'Tipo',
                'WorkStatus' => 'Estado',
                'WorkDate' => 'Data',
                'SystemEntryDate' => 'Data Sistema',
                'CustomerID' => 'Cliente',
                'NetTotal' => 'Base',
                'TaxPayable' => 'IVA',
                'GrossTotal' => 'Total',
            ],
            'general_ledger_accounts' => [
                'AccountID' => 'Conta',
                'AccountDescription' => 'Descrição',
                'OpeningDebitBalance' => 'Saldo Abertura Débito',
                'OpeningCreditBalance' => 'Saldo Abertura Crédito',
                'ClosingDebitBalance' => 'Saldo Fecho Débito',
                'ClosingCreditBalance' => 'Saldo Fecho Crédito',
                'GroupingCategory' => 'Categoria',
                'GroupingCode' => 'Código Agrupamento',
                'TaxonomyCode' => 'Código Taxonomia',
            ],
            'general_ledger_entries' => [
                'JournalID' => 'Diário',
                'JournalDescription' => 'Desc. Diário',
                'TransactionID' => 'Transação',
                'Period' => 'Período',
                'TransactionDate' => 'Data',
                'TransactionType' => 'Tipo',
                'Description' => 'Descrição',
                'SourceID' => 'Utilizador',
                'DocArchivalNumber' => 'Nº Arquivo',
                'SystemEntryDate' => 'Data Sistema',
            ],
            default => [],
        };
    }
}
