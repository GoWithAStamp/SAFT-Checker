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

        foreach ($salesInvoices->Invoice as $invoice) {
            $invoiceNo = (string) $invoice->InvoiceNo;

            if (isset($invoiceNumbers[$invoiceNo])) {
                $this->addError(
                    'SALES_INVOICE_DUPLICATE',
                    "Fatura duplicada: {$invoiceNo}.",
                    'sales_invoices',
                    'InvoiceNo'
                );
            }
            $invoiceNumbers[$invoiceNo] = true;

            $this->validateInvoiceNumber($invoiceNo);
            $this->validateInvoiceStatus($invoice, $invoiceNo);
            $this->validateInvoiceDates($invoice, $invoiceNo);
            $this->validateInvoiceLines($invoice, $invoiceNo);
            $this->validateDocumentTotals($invoice, $invoiceNo, $calculatedDebit, $calculatedCredit);
        }

        $declaredDebit = (float) $this->nodeValue($salesInvoices, 'TotalDebit');
        $declaredCredit = (float) $this->nodeValue($salesInvoices, 'TotalCredit');

        if (abs($declaredDebit - $calculatedDebit) > 0.01) {
            $this->addError(
                'SALES_TOTAL_DEBIT_MISMATCH',
                "TotalDebit declarado ({$declaredDebit}) difere do calculado ({$calculatedDebit}).",
                'sales_invoices',
                'TotalDebit'
            );
        }

        if (abs($declaredCredit - $calculatedCredit) > 0.01) {
            $this->addError(
                'SALES_TOTAL_CREDIT_MISMATCH',
                "TotalCredit declarado ({$declaredCredit}) difere do calculado ({$calculatedCredit}).",
                'sales_invoices',
                'TotalCredit'
            );
        }

        $this->addInfo(
            'SALES_INVOICES_COUNT',
            "Total de faturas: " . count($invoiceNumbers),
            'sales_invoices'
        );
    }

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

    protected function validateInvoiceDates(\SimpleXMLElement $invoice, string $invoiceNo): void
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
    }

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
