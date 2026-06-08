<?php

namespace App\Services\SaftValidator;

class SaftDataExtractor
{
    public function __construct(
        protected \SimpleXMLElement $xml,
    ) {}

    public static function fromFile(string $filePath): self
    {
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \RuntimeException('Não foi possível carregar o ficheiro SAFT-PT.');
        }
        return new self($xml);
    }

    public function extractAll(): array
    {
        return [
            'header' => $this->extractHeader(),
            'customers' => $this->extractCustomers(),
            'suppliers' => $this->extractSuppliers(),
            'products' => $this->extractProducts(),
            'tax_table' => $this->extractTaxTable(),
            'invoices' => $this->extractInvoices(),
            'payments' => $this->extractPayments(),
            'movements' => $this->extractMovements(),
            'working_documents' => $this->extractWorkingDocuments(),
        ];
    }

    protected function extractHeader(): array
    {
        $h = $this->xml->Header;
        if (!$h) return [];

        return [
            'AuditFileVersion' => (string) ($h->AuditFileVersion ?? ''),
            'CompanyID' => (string) ($h->CompanyID ?? ''),
            'TaxRegistrationNumber' => (string) ($h->TaxRegistrationNumber ?? ''),
            'TaxAccountingBasis' => (string) ($h->TaxAccountingBasis ?? ''),
            'CompanyName' => (string) ($h->CompanyName ?? ''),
            'BusinessName' => (string) ($h->BusinessName ?? ''),
            'FiscalYear' => (string) ($h->FiscalYear ?? ''),
            'StartDate' => (string) ($h->StartDate ?? ''),
            'EndDate' => (string) ($h->EndDate ?? ''),
            'CurrencyCode' => (string) ($h->CurrencyCode ?? ''),
            'DateCreated' => (string) ($h->DateCreated ?? ''),
            'TaxEntity' => (string) ($h->TaxEntity ?? ''),
            'ProductCompanyTaxID' => (string) ($h->ProductCompanyTaxID ?? ''),
            'SoftwareCertificateNumber' => (string) ($h->SoftwareCertificateNumber ?? ''),
            'ProductID' => (string) ($h->ProductID ?? ''),
            'ProductVersion' => (string) ($h->ProductVersion ?? ''),
            'Telephone' => (string) ($h->Telephone ?? ''),
            'Email' => (string) ($h->Email ?? ''),
            'Website' => (string) ($h->Website ?? ''),
            'AddressDetail' => (string) ($h->CompanyAddress->AddressDetail ?? ''),
            'City' => (string) ($h->CompanyAddress->City ?? ''),
            'PostalCode' => (string) ($h->CompanyAddress->PostalCode ?? ''),
            'Country' => (string) ($h->CompanyAddress->Country ?? ''),
        ];
    }

    protected function extractCustomers(): array
    {
        $customers = [];
        $mf = $this->xml->MasterFiles;
        if (!$mf || !isset($mf->Customer)) return [];

        foreach ($mf->Customer as $c) {
            $customers[] = [
                'CustomerID' => (string) ($c->CustomerID ?? ''),
                'CustomerTaxID' => (string) ($c->CustomerTaxID ?? ''),
                'CompanyName' => (string) ($c->CompanyName ?? ''),
                'Contact' => (string) ($c->Contact ?? ''),
                'Telephone' => (string) ($c->Telephone ?? ''),
                'Email' => (string) ($c->Email ?? ''),
                'AddressDetail' => (string) ($c->BillingAddress->AddressDetail ?? ''),
                'City' => (string) ($c->BillingAddress->City ?? ''),
                'PostalCode' => (string) ($c->BillingAddress->PostalCode ?? ''),
                'Country' => (string) ($c->BillingAddress->Country ?? ''),
                'SelfBillingIndicator' => (string) ($c->SelfBillingIndicator ?? ''),
            ];
        }
        return $customers;
    }

    protected function extractSuppliers(): array
    {
        $suppliers = [];
        $mf = $this->xml->MasterFiles;
        if (!$mf || !isset($mf->Supplier)) return [];

        foreach ($mf->Supplier as $s) {
            $suppliers[] = [
                'SupplierID' => (string) ($s->SupplierID ?? ''),
                'SupplierTaxID' => (string) ($s->SupplierTaxID ?? ''),
                'CompanyName' => (string) ($s->CompanyName ?? ''),
                'Contact' => (string) ($s->Contact ?? ''),
                'Telephone' => (string) ($s->Telephone ?? ''),
                'Email' => (string) ($s->Email ?? ''),
                'AddressDetail' => (string) ($s->BillingAddress->AddressDetail ?? ''),
                'City' => (string) ($s->BillingAddress->City ?? ''),
                'PostalCode' => (string) ($s->BillingAddress->PostalCode ?? ''),
                'Country' => (string) ($s->BillingAddress->Country ?? ''),
                'SelfBillingIndicator' => (string) ($s->SelfBillingIndicator ?? ''),
            ];
        }
        return $suppliers;
    }

    protected function extractProducts(): array
    {
        $products = [];
        $mf = $this->xml->MasterFiles;
        if (!$mf || !isset($mf->Product)) return [];

        foreach ($mf->Product as $p) {
            $products[] = [
                'ProductType' => (string) ($p->ProductType ?? ''),
                'ProductCode' => (string) ($p->ProductCode ?? ''),
                'ProductGroup' => (string) ($p->ProductGroup ?? ''),
                'ProductDescription' => (string) ($p->ProductDescription ?? ''),
                'ProductNumberCode' => (string) ($p->ProductNumberCode ?? ''),
            ];
        }
        return $products;
    }

    protected function extractTaxTable(): array
    {
        $entries = [];
        $mf = $this->xml->MasterFiles;
        if (!$mf || !isset($mf->TaxTable->TaxTableEntry)) return [];

        foreach ($mf->TaxTable->TaxTableEntry as $t) {
            $entries[] = [
                'TaxType' => (string) ($t->TaxType ?? ''),
                'TaxCountryRegion' => (string) ($t->TaxCountryRegion ?? ''),
                'TaxCode' => (string) ($t->TaxCode ?? ''),
                'Description' => (string) ($t->Description ?? ''),
                'TaxPercentage' => (string) ($t->TaxPercentage ?? ''),
                'TaxAmount' => (string) ($t->TaxAmount ?? ''),
                'TaxExpirationDate' => (string) ($t->TaxExpirationDate ?? ''),
            ];
        }
        return $entries;
    }

    protected function extractInvoices(): array
    {
        $invoices = [];
        $sd = $this->xml->SourceDocuments;
        if (!$sd || !isset($sd->SalesInvoices->Invoice)) return [];

        foreach ($sd->SalesInvoices->Invoice as $inv) {
            $lines = [];
            if (isset($inv->Line)) {
                foreach ($inv->Line as $line) {
                    $lines[] = [
                        'LineNumber' => (string) ($line->LineNumber ?? ''),
                        'ProductCode' => (string) ($line->ProductCode ?? ''),
                        'ProductDescription' => (string) ($line->ProductDescription ?? ''),
                        'Quantity' => (string) ($line->Quantity ?? ''),
                        'UnitOfMeasure' => (string) ($line->UnitOfMeasure ?? ''),
                        'UnitPrice' => (string) ($line->UnitPrice ?? ''),
                        'DebitAmount' => (string) ($line->DebitAmount ?? ''),
                        'CreditAmount' => (string) ($line->CreditAmount ?? ''),
                        'TaxType' => (string) ($line->Tax->TaxType ?? ''),
                        'TaxPercentage' => (string) ($line->Tax->TaxPercentage ?? ''),
                        'TaxExemptionReason' => (string) ($line->TaxExemptionReason ?? ''),
                        'TaxExemptionCode' => (string) ($line->TaxExemptionCode ?? ''),
                    ];
                }
            }

            $invoices[] = [
                'InvoiceNo' => (string) ($inv->InvoiceNo ?? ''),
                'ATCUD' => (string) ($inv->ATCUD ?? ''),
                'InvoiceStatus' => (string) ($inv->DocumentStatus->InvoiceStatus ?? ''),
                'InvoiceDate' => (string) ($inv->InvoiceDate ?? ''),
                'InvoiceType' => (string) ($inv->InvoiceType ?? ''),
                'SourceBilling' => (string) ($inv->DocumentStatus->SourceBilling ?? ''),
                'SystemEntryDate' => (string) ($inv->SystemEntryDate ?? ''),
                'CustomerID' => (string) ($inv->CustomerID ?? ''),
                'TaxPayable' => (string) ($inv->DocumentTotals->TaxPayable ?? ''),
                'NetTotal' => (string) ($inv->DocumentTotals->NetTotal ?? ''),
                'GrossTotal' => (string) ($inv->DocumentTotals->GrossTotal ?? ''),
                'Lines' => $lines,
            ];
        }
        return $invoices;
    }

    protected function extractPayments(): array
    {
        $payments = [];
        $sd = $this->xml->SourceDocuments;
        if (!$sd || !isset($sd->Payments->Payment)) return [];

        foreach ($sd->Payments->Payment as $pay) {
            $lines = [];
            if (isset($pay->Line)) {
                foreach ($pay->Line as $line) {
                    $lines[] = [
                        'LineNumber' => (string) ($line->LineNumber ?? ''),
                        'SourceDocumentID' => (string) ($line->SourceDocumentID->OriginatingON ?? ''),
                        'DebitAmount' => (string) ($line->DebitAmount ?? ''),
                        'CreditAmount' => (string) ($line->CreditAmount ?? ''),
                        'TaxPercentage' => (string) ($line->Tax->TaxPercentage ?? ''),
                    ];
                }
            }

            $payments[] = [
                'PaymentRefNo' => (string) ($pay->PaymentRefNo ?? ''),
                'ATCUD' => (string) ($pay->ATCUD ?? ''),
                'PaymentStatus' => (string) ($pay->DocumentStatus->PaymentStatus ?? ''),
                'PaymentType' => (string) ($pay->PaymentType ?? ''),
                'TransactionDate' => (string) ($pay->TransactionDate ?? ''),
                'SystemEntryDate' => (string) ($pay->SystemEntryDate ?? ''),
                'CustomerID' => (string) ($pay->CustomerID ?? ''),
                'TaxPayable' => (string) ($pay->DocumentTotals->TaxPayable ?? ''),
                'NetTotal' => (string) ($pay->DocumentTotals->NetTotal ?? ''),
                'GrossTotal' => (string) ($pay->DocumentTotals->GrossTotal ?? ''),
                'Lines' => $lines,
            ];
        }
        return $payments;
    }

    protected function extractMovements(): array
    {
        $movements = [];
        $sd = $this->xml->SourceDocuments;
        if (!$sd || !isset($sd->MovementOfGoods->StockMovement)) return [];

        foreach ($sd->MovementOfGoods->StockMovement as $mov) {
            $movements[] = [
                'DocumentNumber' => (string) ($mov->DocumentNumber ?? ''),
                'ATCUD' => (string) ($mov->ATCUD ?? ''),
                'MovementStatus' => (string) ($mov->DocumentStatus->MovementStatus ?? ''),
                'MovementDate' => (string) ($mov->MovementDate ?? ''),
                'MovementType' => (string) ($mov->MovementType ?? ''),
                'SystemEntryDate' => (string) ($mov->SystemEntryDate ?? ''),
                'CustomerID' => (string) ($mov->CustomerID ?? ''),
                'ShipFrom' => (string) ($mov->ShipFrom->Address->AddressDetail ?? ''),
                'ShipTo' => (string) ($mov->ShipTo->Address->AddressDetail ?? ''),
                'MovementStartTime' => (string) ($mov->MovementStartTime ?? ''),
            ];
        }
        return $movements;
    }

    protected function extractWorkingDocuments(): array
    {
        $docs = [];
        $sd = $this->xml->SourceDocuments;
        if (!$sd || !isset($sd->WorkingDocuments->WorkDocument)) return [];

        foreach ($sd->WorkingDocuments->WorkDocument as $doc) {
            $docs[] = [
                'DocumentNumber' => (string) ($doc->DocumentNumber ?? ''),
                'ATCUD' => (string) ($doc->ATCUD ?? ''),
                'WorkStatus' => (string) ($doc->DocumentStatus->WorkStatus ?? ''),
                'WorkDate' => (string) ($doc->WorkDate ?? ''),
                'WorkType' => (string) ($doc->WorkType ?? ''),
                'SystemEntryDate' => (string) ($doc->SystemEntryDate ?? ''),
                'CustomerID' => (string) ($doc->CustomerID ?? ''),
                'TaxPayable' => (string) ($doc->DocumentTotals->TaxPayable ?? ''),
                'NetTotal' => (string) ($doc->DocumentTotals->NetTotal ?? ''),
                'GrossTotal' => (string) ($doc->DocumentTotals->GrossTotal ?? ''),
            ];
        }
        return $docs;
    }
}
