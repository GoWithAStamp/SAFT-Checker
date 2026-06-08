<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

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

        foreach ($payments->Payment as $payment) {
            $paymentNo = (string) $payment->PaymentRefNo;

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
            $this->validatePaymentDates($payment, $paymentNo);
            $this->validatePaymentTotals($payment, $paymentNo);
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

    protected function validatePaymentDates(\SimpleXMLElement $payment, string $paymentNo): void
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
    }

    protected function validatePaymentTotals(\SimpleXMLElement $payment, string $paymentNo): void
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
    }
}
