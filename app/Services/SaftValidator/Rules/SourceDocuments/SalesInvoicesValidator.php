<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

class SalesInvoicesValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $sourceDocuments = $this->xml->SourceDocuments;
        if (!$sourceDocuments || !isset($sourceDocuments->SalesInvoices)) {
            return $this->result();
        }

        $salesInvoices = $sourceDocuments->SalesInvoices;

        $this->validateTotals($salesInvoices);
        $this->validateInvoices($salesInvoices);

        return $this->result();
    }

    protected function validateTotals(\SimpleXMLElement $salesInvoices): void
    {
        $numberOfEntries = $this->nodeValue($salesInvoices, 'NumberOfEntries');
        $totalDebit = $this->nodeValue($salesInvoices, 'TotalDebit');
        $totalCredit = $this->nodeValue($salesInvoices, 'TotalCredit');

        $actualCount = isset($salesInvoices->Invoice) ? count($salesInvoices->Invoice) : 0;

        if ($numberOfEntries !== null && (int) $numberOfEntries !== $actualCount) {
            $this->addError(
                'SALES_NUMBER_ENTRIES_MISMATCH',
                "NumberOfEntries ({$numberOfEntries}) não corresponde ao número real de faturas ({$actualCount}).",
                'sales_invoices',
                'NumberOfEntries'
            );
        }

        if ($totalDebit === null) {
            $this->addError('SALES_TOTAL_DEBIT_MISSING', 'TotalDebit em falta em SalesInvoices.', 'sales_invoices', 'TotalDebit');
        }

        if ($totalCredit === null) {
            $this->addError('SALES_TOTAL_CREDIT_MISSING', 'TotalCredit em falta em SalesInvoices.', 'sales_invoices', 'TotalCredit');
        }
    }

    protected function validateInvoices(\SimpleXMLElement $salesInvoices): void
    {
        if (!isset($salesInvoices->Invoice)) {
            return;
        }

        $invoiceNumbers = [];
        $calculatedDebit = 0.0;
        $calculatedCredit = 0.0;
        $seriesNumbers = []; // track per-series for sequence gap detection
        $headerStartDate = (string) ($this->xml->Header->StartDate ?? '');
        $headerEndDate = (string) ($this->xml->Header->EndDate ?? '');

        foreach ($salesInvoices->Invoice as $invoice) {
            $invoiceNo = (string) $invoice->InvoiceNo;

            // — Duplicate detection
            if (isset($invoiceNumbers[$invoiceNo])) {
                $this->addError(
                    'SALES_INVOICE_DUPLICATE',
                    "Fatura duplicada: {$invoiceNo}.",
                    'sales_invoices',
                    'InvoiceNo'
                );
            }
            $invoiceNumbers[$invoiceNo] = true;

            // — Collect series/number for gap detection
            if (preg_match('/^([A-Z]{2,4} [A-Z0-9]+\/)(\d+)$/', $invoiceNo, $m)) {
                $series = $m[1];
                $number = (int) $m[2];
                $seriesNumbers[$series][] = $number;
            }

            $this->validateInvoiceNumber($invoiceNo);
            $this->validateATCUD($invoice, $invoiceNo);
            $this->validateInvoiceStatus($invoice, $invoiceNo);
            $this->validateInvoiceDates($invoice, $invoiceNo, $headerStartDate, $headerEndDate);
            $this->validatePeriodConsistency($invoice, $invoiceNo);
            $this->validateInvoiceLines($invoice, $invoiceNo);
            $this->validateDocumentTotals($invoice, $invoiceNo, $calculatedDebit, $calculatedCredit);
            $this->validateLineMathTotals($invoice, $invoiceNo);
            $this->validateCustomerExists($invoice, $invoiceNo);
        }

        // — Section-level debit/credit check
        $declaredDebit = (float) $this->nodeValue($salesInvoices, 'TotalDebit');
        $declaredCredit = (float) $this->nodeValue($salesInvoices, 'TotalCredit');

        if (abs($declaredDebit - $calculatedDebit) > 0.01) {
            $this->addError(
                'SALES_TOTAL_DEBIT_MISMATCH',
                "TotalDebit declarado ({$declaredDebit}) difere do calculado (" . round($calculatedDebit, 2) . ").",
                'sales_invoices',
                'TotalDebit'
            );
        }

        if (abs($declaredCredit - $calculatedCredit) > 0.01) {
            $this->addError(
                'SALES_TOTAL_CREDIT_MISMATCH',
                "TotalCredit declarado ({$declaredCredit}) difere do calculado (" . round($calculatedCredit, 2) . ").",
                'sales_invoices',
                'TotalCredit'
            );
        }

        // — Sequence gap detection per series
        foreach ($seriesNumbers as $series => $numbers) {
            sort($numbers);
            for ($i = 1; $i < count($numbers); $i++) {
                $expected = $numbers[$i - 1] + 1;
                $actual = $numbers[$i];
                if ($actual !== $expected) {
                    $missing = [];
                    for ($n = $expected; $n < $actual && count($missing) < 10; $n++) {
                        $missing[] = $series . $n;
                    }
                    $missingStr = implode(', ', $missing);
                    if ($actual - $expected > 10) {
                        $missingStr .= ', ...';
                    }
                    $this->addWarning(
                        'SALES_INVOICE_SEQUENCE_GAP',
                        "Lacuna na numeração da série {$series}: falta(m) {$missingStr}.",
                        'sales_invoices',
                        'InvoiceNo',
                        'A numeração sequencial dos documentos é obrigatória. Lacunas devem ser justificadas.'
                    );
                }
            }
        }

        $this->addInfo(
            'SALES_INVOICES_COUNT',
            "Total de faturas: " . count($invoiceNumbers),
            'sales_invoices'
        );
    }

    // ── ATCUD validation ──────────────────────────────────────

    protected function validateATCUD(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        $atcud = $this->nodeValue($invoice, 'ATCUD');

        if ($atcud === null) {
            $this->addError(
                'SALES_INVOICE_ATCUD_MISSING',
                "ATCUD em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'ATCUD'
            );
            return;
        }

        // ATCUD format: validation code + hyphen + sequential number
        // Or "0" if exempt (pre-2023 transition period)
        if ($atcud !== '0' && !preg_match('/^[A-Z0-9]+-\d+$/', $atcud)) {
            $this->addWarning(
                'SALES_INVOICE_ATCUD_FORMAT',
                "Formato ATCUD possivelmente inválido na fatura {$invoiceNo}: {$atcud}. Esperado: CODIGO-SEQUENCIAL.",
                'sales_invoices',
                'ATCUD'
            );
        }
    }

    // ── Period consistency ─────────────────────────────────────

    protected function validatePeriodConsistency(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        $period = $this->nodeValue($invoice, 'Period');
        $invoiceDate = $this->nodeValue($invoice, 'InvoiceDate');

        if ($period === null || $invoiceDate === null) {
            return;
        }

        $month = (int) date('n', strtotime($invoiceDate));
        $declaredPeriod = (int) $period;

        if ($declaredPeriod !== $month) {
            $this->addWarning(
                'SALES_INVOICE_PERIOD_MISMATCH',
                "Período ({$period}) não corresponde ao mês da fatura ({$month}) em {$invoiceNo}.",
                'sales_invoices',
                'Period',
                "InvoiceDate: {$invoiceDate}, mês esperado: {$month}."
            );
        }
    }

    // ── Date range check against header ───────────────────────

    protected function validateInvoiceDates(\SimpleXMLElement $invoice, string $invoiceNo, string $headerStartDate, string $headerEndDate): void
    {
        $invoiceDate = $this->nodeValue($invoice, 'InvoiceDate');
        $systemEntryDate = $this->nodeValue($invoice, 'SystemEntryDate');

        if ($invoiceDate === null) {
            $this->addError(
                'SALES_INVOICE_DATE_MISSING',
                "InvoiceDate em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'InvoiceDate'
            );
        }

        if ($systemEntryDate === null) {
            $this->addError(
                'SALES_INVOICE_SYSTEM_DATE_MISSING',
                "SystemEntryDate em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'SystemEntryDate'
            );
        }

        if ($invoiceDate && $systemEntryDate) {
            $invoiceDateOnly = substr($invoiceDate, 0, 10);
            $systemDateOnly = substr($systemEntryDate, 0, 10);

            if ($systemDateOnly < $invoiceDateOnly) {
                $this->addWarning(
                    'SALES_INVOICE_DATE_INCONSISTENCY',
                    "SystemEntryDate ({$systemDateOnly}) anterior a InvoiceDate ({$invoiceDateOnly}) na fatura {$invoiceNo}.",
                    'sales_invoices',
                    'SystemEntryDate'
                );
            }
        }

        // Check invoice date within header date range
        if ($invoiceDate && $headerStartDate && $headerEndDate) {
            $invoiceDateOnly = substr($invoiceDate, 0, 10);
            if ($invoiceDateOnly < $headerStartDate || $invoiceDateOnly > $headerEndDate) {
                $this->addError(
                    'SALES_INVOICE_DATE_OUT_OF_RANGE',
                    "InvoiceDate ({$invoiceDateOnly}) fora do período do ficheiro ({$headerStartDate} a {$headerEndDate}) na fatura {$invoiceNo}.",
                    'sales_invoices',
                    'InvoiceDate',
                    'Todas as faturas devem ter data dentro do período declarado no Header.'
                );
            }
        }
    }

    // ── Customer exists in MasterFiles ────────────────────────

    protected function validateCustomerExists(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        $customerID = $this->nodeValue($invoice, 'CustomerID');
        if ($customerID === null) {
            $this->addError(
                'SALES_INVOICE_CUSTOMER_MISSING',
                "CustomerID em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'CustomerID'
            );
            return;
        }

        // Check if customer exists in MasterFiles
        $found = false;
        if (isset($this->xml->MasterFiles->Customer)) {
            foreach ($this->xml->MasterFiles->Customer as $customer) {
                if ((string) $customer->CustomerID === $customerID) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $this->addError(
                'SALES_INVOICE_CUSTOMER_NOT_FOUND',
                "Cliente {$customerID} referenciado na fatura {$invoiceNo} não existe em MasterFiles.",
                'sales_invoices',
                'CustomerID',
                'Todos os clientes referenciados em documentos devem constar na secção MasterFiles.'
            );
        }
    }

    // ── Invoice number format ─────────────────────────────────

    protected function validateInvoiceNumber(string $invoiceNo): void
    {
        if (!preg_match('/^[A-Z]{2,4} [A-Z0-9]+\/\d+$/', $invoiceNo)) {
            $this->addWarning(
                'SALES_INVOICE_NUMBER_FORMAT',
                "Formato de número de fatura possivelmente inválido: {$invoiceNo}. Esperado: TIPO SERIE/NUMERO.",
                'sales_invoices',
                'InvoiceNo'
            );
        }
    }

    // ── Status validation ─────────────────────────────────────

    protected function validateInvoiceStatus(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        $status = $invoice->DocumentStatus;
        if (!$status) {
            $this->addError(
                'SALES_INVOICE_STATUS_MISSING',
                "DocumentStatus em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'DocumentStatus'
            );
            return;
        }

        $invoiceStatus = $this->nodeValue($status, 'InvoiceStatus');
        $validStatuses = ['N', 'S', 'A', 'R', 'F'];
        if ($invoiceStatus !== null && !in_array($invoiceStatus, $validStatuses)) {
            $this->addError(
                'SALES_INVOICE_STATUS_INVALID',
                "Estado inválido na fatura {$invoiceNo}: {$invoiceStatus}.",
                'sales_invoices',
                'InvoiceStatus'
            );
        }

        // Cancelled invoices must have a Reason
        if ($invoiceStatus === 'A') {
            $reason = $this->nodeValue($status, 'Reason');
            if ($reason === null) {
                $this->addWarning(
                    'SALES_INVOICE_CANCEL_NO_REASON',
                    "Fatura anulada {$invoiceNo} sem motivo (Reason).",
                    'sales_invoices',
                    'Reason',
                    'Faturas anuladas devem indicar o motivo da anulação.'
                );
            }
        }

        $sourceBilling = $this->nodeValue($status, 'SourceBilling');
        $validSources = ['P', 'I', 'M'];
        if ($sourceBilling !== null && !in_array($sourceBilling, $validSources)) {
            $this->addError(
                'SALES_INVOICE_SOURCE_BILLING_INVALID',
                "SourceBilling inválido na fatura {$invoiceNo}: {$sourceBilling}.",
                'sales_invoices',
                'SourceBilling'
            );
        }
    }

    // ── Line-level validations ────────────────────────────────

    protected function validateInvoiceLines(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        if (!isset($invoice->Line)) {
            $this->addError(
                'SALES_INVOICE_NO_LINES',
                "Fatura {$invoiceNo} sem linhas.",
                'sales_invoices',
                'Line'
            );
            return;
        }

        $lineNumbers = [];
        foreach ($invoice->Line as $line) {
            $lineNumber = $this->nodeValue($line, 'LineNumber');

            if ($lineNumber !== null && isset($lineNumbers[$lineNumber])) {
                $this->addError(
                    'SALES_INVOICE_LINE_DUPLICATE',
                    "Linha duplicada na fatura {$invoiceNo}: linha {$lineNumber}.",
                    'sales_invoices',
                    'LineNumber'
                );
            }
            if ($lineNumber !== null) {
                $lineNumbers[$lineNumber] = true;
            }

            $this->validateLineTax($line, $invoiceNo, $lineNumber);
            $this->validateLineAmounts($line, $invoiceNo, $lineNumber);
            $this->validateLineUnitPrice($line, $invoiceNo, $lineNumber);
            $this->validateLineProductExists($line, $invoiceNo, $lineNumber);
        }
    }

    protected function validateLineTax(\SimpleXMLElement $line, string $invoiceNo, ?string $lineNumber): void
    {
        $tax = $line->Tax;
        if (!$tax) {
            $debitAmount = $this->nodeValue($line, 'DebitAmount');
            $creditAmount = $this->nodeValue($line, 'CreditAmount');
            $amount = $debitAmount ?? $creditAmount ?? '0';

            if ((float) $amount > 0) {
                $this->addError(
                    'SALES_INVOICE_LINE_TAX_MISSING',
                    "Imposto em falta na fatura {$invoiceNo}, linha {$lineNumber}.",
                    'sales_invoices',
                    'Tax'
                );
            }
            return;
        }

        $taxPercentage = $this->nodeValue($tax, 'TaxPercentage');
        if ($taxPercentage !== null && (float) $taxPercentage === 0.0) {
            $exemptionReason = $this->nodeValue($line, 'TaxExemptionReason');
            $exemptionCode = $this->nodeValue($line, 'TaxExemptionCode');

            if ($exemptionReason === null && $exemptionCode === null) {
                $this->addError(
                    'SALES_INVOICE_LINE_EXEMPTION_MISSING',
                    "Taxa 0% sem motivo de isenção na fatura {$invoiceNo}, linha {$lineNumber}.",
                    'sales_invoices',
                    'TaxExemptionReason',
                    'Quando a taxa é 0%, é obrigatório indicar o motivo e código de isenção.'
                );
            }
        }
    }

    protected function validateLineAmounts(\SimpleXMLElement $line, string $invoiceNo, ?string $lineNumber): void
    {
        $debitAmount = $this->nodeValue($line, 'DebitAmount');
        $creditAmount = $this->nodeValue($line, 'CreditAmount');

        if ($debitAmount === null && $creditAmount === null) {
            $this->addError(
                'SALES_INVOICE_LINE_AMOUNT_MISSING',
                "DebitAmount e CreditAmount ambos em falta na fatura {$invoiceNo}, linha {$lineNumber}.",
                'sales_invoices',
                'DebitAmount/CreditAmount'
            );
        }

        if ($debitAmount !== null && $creditAmount !== null) {
            $this->addError(
                'SALES_INVOICE_LINE_BOTH_AMOUNTS',
                "DebitAmount e CreditAmount ambos presentes na fatura {$invoiceNo}, linha {$lineNumber}. Apenas um deve existir.",
                'sales_invoices',
                'DebitAmount/CreditAmount'
            );
        }
    }

    protected function validateLineUnitPrice(\SimpleXMLElement $line, string $invoiceNo, ?string $lineNumber): void
    {
        $quantity = $this->nodeValue($line, 'Quantity');
        $unitPrice = $this->nodeValue($line, 'UnitPrice');
        $debitAmount = $this->nodeValue($line, 'DebitAmount');
        $creditAmount = $this->nodeValue($line, 'CreditAmount');
        $lineAmount = (float) ($creditAmount ?? $debitAmount ?? '0');

        if ($quantity !== null && $unitPrice !== null && $lineAmount > 0) {
            $calculated = round((float) $quantity * (float) $unitPrice, 2);
            if (abs($calculated - $lineAmount) > 0.01) {
                $this->addWarning(
                    'SALES_INVOICE_LINE_AMOUNT_CALC',
                    "Montante da linha {$lineNumber} na fatura {$invoiceNo}: Qty ({$quantity}) × Preço ({$unitPrice}) = " . number_format($calculated, 2, '.', '') . ", declarado: " . number_format($lineAmount, 2, '.', '') . ".",
                    'sales_invoices',
                    'CreditAmount/DebitAmount',
                    'Pode indicar desconto aplicado ou erro de cálculo.'
                );
            }
        }
    }

    protected function validateLineProductExists(\SimpleXMLElement $line, string $invoiceNo, ?string $lineNumber): void
    {
        $productCode = $this->nodeValue($line, 'ProductCode');
        if ($productCode === null) {
            return;
        }

        $found = false;
        if (isset($this->xml->MasterFiles->Product)) {
            foreach ($this->xml->MasterFiles->Product as $product) {
                if ((string) $product->ProductCode === $productCode) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $this->addError(
                'SALES_INVOICE_LINE_PRODUCT_NOT_FOUND',
                "Produto {$productCode} referenciado na fatura {$invoiceNo}, linha {$lineNumber}, não existe em MasterFiles.",
                'sales_invoices',
                'ProductCode'
            );
        }
    }

    // ── Tax math verification (recalculate from lines) ────────

    protected function validateLineMathTotals(\SimpleXMLElement $invoice, string $invoiceNo): void
    {
        $totals = $invoice->DocumentTotals;
        $invoiceStatus = $this->nodeValue($invoice->DocumentStatus, 'InvoiceStatus');

        // Skip math check for cancelled invoices
        if ($invoiceStatus === 'A' || !$totals || !isset($invoice->Line)) {
            return;
        }

        $declaredNetTotal = (float) $this->nodeValue($totals, 'NetTotal');
        $declaredTaxPayable = (float) $this->nodeValue($totals, 'TaxPayable');

        $calculatedNetTotal = 0.0;
        $calculatedTax = 0.0;

        foreach ($invoice->Line as $line) {
            $debitAmount = $this->nodeValue($line, 'DebitAmount');
            $creditAmount = $this->nodeValue($line, 'CreditAmount');
            $lineAmount = (float) ($creditAmount ?? $debitAmount ?? '0');

            $calculatedNetTotal += $lineAmount;

            // Calculate tax from line amount × percentage
            $tax = $line->Tax;
            if ($tax) {
                $taxPercentage = (float) $this->nodeValue($tax, 'TaxPercentage');
                $calculatedTax += round($lineAmount * $taxPercentage / 100, 2);
            }
        }

        $calculatedNetTotal = round($calculatedNetTotal, 2);
        $calculatedTax = round($calculatedTax, 2);

        // NetTotal vs sum of line amounts
        if (abs($declaredNetTotal - $calculatedNetTotal) > 0.01) {
            $this->addError(
                'SALES_INVOICE_NET_TOTAL_MISMATCH',
                "NetTotal ({$declaredNetTotal}) difere da soma das linhas (" . number_format($calculatedNetTotal, 2, '.', '') . ") na fatura {$invoiceNo}.",
                'sales_invoices',
                'NetTotal',
                'O NetTotal deve ser igual à soma dos montantes das linhas.'
            );
        }

        // TaxPayable vs calculated tax from lines
        if (abs($declaredTaxPayable - $calculatedTax) > 0.01) {
            $this->addError(
                'SALES_INVOICE_TAX_PAYABLE_MISMATCH',
                "TaxPayable ({$declaredTaxPayable}) difere do imposto calculado (" . number_format($calculatedTax, 2, '.', '') . ") na fatura {$invoiceNo}.",
                'sales_invoices',
                'TaxPayable',
                'O TaxPayable deve corresponder à soma do imposto calculado por linha (montante × taxa / 100).'
            );
        }
    }

    // ── Document totals (GrossTotal check + accumulate) ───────

    protected function validateDocumentTotals(\SimpleXMLElement $invoice, string $invoiceNo, float &$totalDebit, float &$totalCredit): void
    {
        $totals = $invoice->DocumentTotals;
        if (!$totals) {
            $this->addError(
                'SALES_INVOICE_TOTALS_MISSING',
                "DocumentTotals em falta na fatura {$invoiceNo}.",
                'sales_invoices',
                'DocumentTotals'
            );
            return;
        }

        $netTotal = (float) $this->nodeValue($totals, 'NetTotal');
        $taxPayable = (float) $this->nodeValue($totals, 'TaxPayable');
        $grossTotal = (float) $this->nodeValue($totals, 'GrossTotal');

        $calculatedGross = $netTotal + $taxPayable;
        if (abs($grossTotal - $calculatedGross) > 0.01) {
            $this->addError(
                'SALES_INVOICE_TOTALS_MISMATCH',
                "GrossTotal ({$grossTotal}) ≠ NetTotal ({$netTotal}) + TaxPayable ({$taxPayable}) na fatura {$invoiceNo}.",
                'sales_invoices',
                'GrossTotal'
            );
        }

        $invoiceStatus = $this->nodeValue($invoice->DocumentStatus, 'InvoiceStatus');
        if ($invoiceStatus !== 'A') {
            $invoiceType = $this->nodeValue($invoice, 'InvoiceType');
            $creditTypes = ['NC', 'ND'];

            if (in_array($invoiceType, $creditTypes)) {
                $totalDebit += $grossTotal;
            } else {
                $totalCredit += $grossTotal;
            }
        }
    }
}
