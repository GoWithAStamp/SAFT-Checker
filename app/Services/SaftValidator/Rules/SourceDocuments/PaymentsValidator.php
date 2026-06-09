<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

/**
 * Validates the Payments section of a SAFT-PT file.
 *
 * Beyond structural checks (duplicates, statuses, dates, totals), this validator
 * performs cross-referencing between payment lines and the SalesInvoices section.
 * Each payment line's SourceDocumentID.OriginatingON is checked against the
 * invoice map to verify: the referenced invoice exists, dates are consistent
 * (payment cannot precede its invoice), and cancelled invoices are not referenced.
 */
class PaymentsValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $sourceDocuments = $this->xml->SourceDocuments;
        if (!$sourceDocuments || !isset($sourceDocuments->Payments)) {
            return $this->result();
        }

        $payments = $sourceDocuments->Payments;

        $this->validateTotals($payments);
        $this->validatePaymentEntries($payments);

        return $this->result();
    }

    protected function validateTotals(\SimpleXMLElement $payments): void
    {
        $numberOfEntries = $this->nodeValue($payments, 'NumberOfEntries');
        $totalDebit = $this->nodeValue($payments, 'TotalDebit');
        $totalCredit = $this->nodeValue($payments, 'TotalCredit');

        $actualCount = isset($payments->Payment) ? count($payments->Payment) : 0;

        if ($numberOfEntries !== null && (int) $numberOfEntries !== $actualCount) {
            $this->addError(
                'PAYMENTS_NUMBER_ENTRIES_MISMATCH',
                "NumberOfEntries ({$numberOfEntries}) não corresponde ao número real de pagamentos ({$actualCount}).",
                'payments',
                'NumberOfEntries'
            );
        }

        if ($totalDebit === null) {
            $this->addError('PAYMENTS_TOTAL_DEBIT_MISSING', 'TotalDebit em falta em Payments.', 'payments', 'TotalDebit');
        }

        if ($totalCredit === null) {
            $this->addError('PAYMENTS_TOTAL_CREDIT_MISSING', 'TotalCredit em falta em Payments.', 'payments', 'TotalCredit');
        }
    }

    protected function validatePaymentEntries(\SimpleXMLElement $payments): void
    {
        if (!isset($payments->Payment)) {
            return;
        }

        $paymentNumbers = [];
        $calculatedDebit = 0.0;
        $calculatedCredit = 0.0;
        $headerStartDate = (string) ($this->xml->Header->StartDate ?? '');
        $headerEndDate = (string) ($this->xml->Header->EndDate ?? '');

        // Build invoice lookup for cross-reference
        $invoiceMap = $this->buildInvoiceMap();

        foreach ($payments->Payment as $payment) {
            $paymentNo = (string) $payment->PaymentRefNo;

            // — Duplicate detection
            if (isset($paymentNumbers[$paymentNo])) {
                $this->addError(
                    'PAYMENTS_DUPLICATE',
                    "Pagamento duplicado: {$paymentNo}.",
                    'payments',
                    'PaymentRefNo'
                );
            }
            $paymentNumbers[$paymentNo] = true;

            $this->validatePaymentStatus($payment, $paymentNo);
            $this->validatePaymentDates($payment, $paymentNo, $headerStartDate, $headerEndDate);
            $this->validatePeriodConsistency($payment, $paymentNo);
            $this->validatePaymentTotals($payment, $paymentNo, $calculatedDebit, $calculatedCredit);
            $this->validatePaymentCustomerExists($payment, $paymentNo);
            $this->validatePaymentCrossReferences($payment, $paymentNo, $invoiceMap);
        }

        // Section-level totals
        $declaredDebit = (float) $this->nodeValue($payments, 'TotalDebit');
        $declaredCredit = (float) $this->nodeValue($payments, 'TotalCredit');

        if (abs($declaredDebit - $calculatedDebit) > 0.01) {
            $this->addError(
                'PAYMENTS_TOTAL_DEBIT_CALC_MISMATCH',
                "TotalDebit declarado ({$declaredDebit}) difere do calculado (" . round($calculatedDebit, 2) . ").",
                'payments',
                'TotalDebit'
            );
        }

        if (abs($declaredCredit - $calculatedCredit) > 0.01) {
            $this->addError(
                'PAYMENTS_TOTAL_CREDIT_CALC_MISMATCH',
                "TotalCredit declarado ({$declaredCredit}) difere do calculado (" . round($calculatedCredit, 2) . ").",
                'payments',
                'TotalCredit'
            );
        }

        $this->addInfo(
            'PAYMENTS_COUNT',
            "Total de pagamentos: " . count($paymentNumbers),
            'payments'
        );
    }

    protected function validatePaymentStatus(\SimpleXMLElement $payment, string $paymentNo): void
    {
        $status = $payment->DocumentStatus;
        if (!$status) {
            $this->addError(
                'PAYMENTS_STATUS_MISSING',
                "DocumentStatus em falta no pagamento {$paymentNo}.",
                'payments',
                'DocumentStatus'
            );
            return;
        }

        $paymentStatus = $this->nodeValue($status, 'PaymentStatus');
        $validStatuses = ['N', 'A'];
        if ($paymentStatus !== null && !in_array($paymentStatus, $validStatuses)) {
            $this->addError(
                'PAYMENTS_STATUS_INVALID',
                "Estado inválido no pagamento {$paymentNo}: {$paymentStatus}.",
                'payments',
                'PaymentStatus'
            );
        }
    }

    protected function validatePaymentDates(\SimpleXMLElement $payment, string $paymentNo, string $headerStartDate, string $headerEndDate): void
    {
        $transactionDate = $this->nodeValue($payment, 'TransactionDate');
        $systemEntryDate = $this->nodeValue($payment, 'SystemEntryDate');

        if ($transactionDate === null) {
            $this->addError(
                'PAYMENTS_DATE_MISSING',
                "TransactionDate em falta no pagamento {$paymentNo}.",
                'payments',
                'TransactionDate'
            );
        }

        if ($systemEntryDate === null) {
            $this->addError(
                'PAYMENTS_SYSTEM_DATE_MISSING',
                "SystemEntryDate em falta no pagamento {$paymentNo}.",
                'payments',
                'SystemEntryDate'
            );
        }

        if ($transactionDate && $systemEntryDate) {
            $txDateOnly = substr($transactionDate, 0, 10);
            $sysDateOnly = substr($systemEntryDate, 0, 10);

            if ($sysDateOnly < $txDateOnly) {
                $this->addWarning(
                    'PAYMENTS_DATE_INCONSISTENCY',
                    "SystemEntryDate ({$sysDateOnly}) anterior a TransactionDate ({$txDateOnly}) no pagamento {$paymentNo}.",
                    'payments',
                    'SystemEntryDate'
                );
            }
        }

        // Date range check
        if ($transactionDate && $headerStartDate && $headerEndDate) {
            $txDateOnly = substr($transactionDate, 0, 10);
            if ($txDateOnly < $headerStartDate || $txDateOnly > $headerEndDate) {
                $this->addError(
                    'PAYMENTS_DATE_OUT_OF_RANGE',
                    "TransactionDate ({$txDateOnly}) fora do período do ficheiro ({$headerStartDate} a {$headerEndDate}) no pagamento {$paymentNo}.",
                    'payments',
                    'TransactionDate'
                );
            }
        }
    }

    protected function validatePeriodConsistency(\SimpleXMLElement $payment, string $paymentNo): void
    {
        $period = $this->nodeValue($payment, 'Period');
        $transactionDate = $this->nodeValue($payment, 'TransactionDate');

        if ($period === null || $transactionDate === null) {
            return;
        }

        $month = (int) date('n', strtotime($transactionDate));
        $declaredPeriod = (int) $period;

        if ($declaredPeriod !== $month) {
            $this->addWarning(
                'PAYMENTS_PERIOD_MISMATCH',
                "Período ({$period}) não corresponde ao mês da transação ({$month}) no pagamento {$paymentNo}.",
                'payments',
                'Period'
            );
        }
    }

    protected function validatePaymentTotals(\SimpleXMLElement $payment, string $paymentNo, float &$totalDebit, float &$totalCredit): void
    {
        $totals = $payment->DocumentTotals;
        if (!$totals) {
            $this->addError(
                'PAYMENTS_TOTALS_MISSING',
                "DocumentTotals em falta no pagamento {$paymentNo}.",
                'payments',
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
                'PAYMENTS_TOTALS_MISMATCH',
                "GrossTotal ({$grossTotal}) ≠ NetTotal ({$netTotal}) + TaxPayable ({$taxPayable}) no pagamento {$paymentNo}.",
                'payments',
                'GrossTotal'
            );
        }

        // Recalculate from lines
        if (isset($payment->Line)) {
            $lineTotal = 0.0;
            foreach ($payment->Line as $line) {
                $creditAmt = (float) $this->nodeValue($line, 'CreditAmount');
                $debitAmt = (float) $this->nodeValue($line, 'DebitAmount');
                $lineTotal += ($creditAmt - $debitAmt);
            }

            if (abs($grossTotal - abs($lineTotal)) > 0.01) {
                $this->addWarning(
                    'PAYMENTS_LINE_TOTAL_MISMATCH',
                    "GrossTotal ({$grossTotal}) difere da soma das linhas (" . number_format(abs($lineTotal), 2, '.', '') . ") no pagamento {$paymentNo}.",
                    'payments',
                    'GrossTotal'
                );
            }
        }

        // Accumulate for section totals
        $paymentStatus = $this->nodeValue($payment->DocumentStatus, 'PaymentStatus');
        if ($paymentStatus !== 'A') {
            $totalCredit += $grossTotal;
        }
    }

    protected function validatePaymentCustomerExists(\SimpleXMLElement $payment, string $paymentNo): void
    {
        $customerID = $this->nodeValue($payment, 'CustomerID');
        if ($customerID === null) {
            return;
        }

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
                'PAYMENTS_CUSTOMER_NOT_FOUND',
                "Cliente {$customerID} referenciado no pagamento {$paymentNo} não existe em MasterFiles.",
                'payments',
                'CustomerID'
            );
        }
    }

    // ── Cross-reference: payment lines → invoices ─────────────

    protected function validatePaymentCrossReferences(\SimpleXMLElement $payment, string $paymentNo, array $invoiceMap): void
    {
        if (!isset($payment->Line)) {
            return;
        }

        foreach ($payment->Line as $line) {
            $sourceDoc = $line->SourceDocumentID;
            if (!$sourceDoc) {
                continue;
            }

            $originatingON = $this->nodeValue($sourceDoc, 'OriginatingON');
            if ($originatingON === null) {
                continue;
            }

            if (!isset($invoiceMap[$originatingON])) {
                $this->addWarning(
                    'PAYMENTS_INVOICE_REF_NOT_FOUND',
                    "Pagamento {$paymentNo} referencia fatura {$originatingON} que não existe em SalesInvoices.",
                    'payments',
                    'OriginatingON',
                    'A fatura referenciada pode pertencer a outro período ou estar em falta.'
                );
                continue;
            }

            // Check invoice date matches
            $invoiceDate = $this->nodeValue($sourceDoc, 'InvoiceDate');
            if ($invoiceDate !== null && $invoiceMap[$originatingON]['date'] !== null) {
                if ($invoiceDate !== $invoiceMap[$originatingON]['date']) {
                    $this->addWarning(
                        'PAYMENTS_INVOICE_DATE_MISMATCH',
                        "Data da fatura no pagamento {$paymentNo} ({$invoiceDate}) difere da data real da fatura {$originatingON} ({$invoiceMap[$originatingON]['date']}).",
                        'payments',
                        'InvoiceDate'
                    );
                }
            }

            // Check payment date is not before invoice date
            $paymentDate = $this->nodeValue($payment, 'TransactionDate');
            if ($paymentDate && $invoiceMap[$originatingON]['date']) {
                if ($paymentDate < $invoiceMap[$originatingON]['date']) {
                    $this->addError(
                        'PAYMENTS_BEFORE_INVOICE',
                        "Pagamento {$paymentNo} ({$paymentDate}) é anterior à fatura {$originatingON} ({$invoiceMap[$originatingON]['date']}).",
                        'payments',
                        'TransactionDate',
                        'Um pagamento não pode ter data anterior à fatura que liquida.'
                    );
                }
            }

            // Check payment references cancelled invoice
            if ($invoiceMap[$originatingON]['status'] === 'A') {
                $this->addError(
                    'PAYMENTS_REF_CANCELLED_INVOICE',
                    "Pagamento {$paymentNo} referencia fatura anulada {$originatingON}.",
                    'payments',
                    'OriginatingON',
                    'Não devem existir pagamentos associados a faturas anuladas.'
                );
            }
        }
    }

    /**
     * Build lookup map of all invoices: InvoiceNo → [date, status, grossTotal]
     */
    protected function buildInvoiceMap(): array
    {
        $map = [];
        $sd = $this->xml->SourceDocuments;
        if (!$sd || !isset($sd->SalesInvoices->Invoice)) {
            return $map;
        }

        foreach ($sd->SalesInvoices->Invoice as $invoice) {
            $invoiceNo = (string) $invoice->InvoiceNo;
            $map[$invoiceNo] = [
                'date' => $this->nodeValue($invoice, 'InvoiceDate'),
                'status' => $this->nodeValue($invoice->DocumentStatus, 'InvoiceStatus'),
                'grossTotal' => (float) $this->nodeValue($invoice->DocumentTotals, 'GrossTotal'),
            ];
        }

        return $map;
    }
}
