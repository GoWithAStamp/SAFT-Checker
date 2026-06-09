<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

/**
 * Validates the WorkingDocuments section of a SAFT-PT file.
 *
 * Working documents include proforma invoices, quotes, purchase orders,
 * and other non-fiscal documents that support the billing workflow.
 * Validates document uniqueness, status codes (N=Normal, A=Cancelled, F=Invoiced),
 * required dates, and the GrossTotal = NetTotal + TaxPayable formula.
 */
class WorkingDocumentsValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $sourceDocuments = $this->xml->SourceDocuments;
        if (!$sourceDocuments || !isset($sourceDocuments->WorkingDocuments)) {
            return $this->result();
        }

        $workingDocs = $sourceDocuments->WorkingDocuments;

        $this->validateTotals($workingDocs);
        $this->validateDocuments($workingDocs);

        return $this->result();
    }

    protected function validateTotals(\SimpleXMLElement $workingDocs): void
    {
        $numberOfEntries = $this->nodeValue($workingDocs, 'NumberOfEntries');
        $totalDebit = $this->nodeValue($workingDocs, 'TotalDebit');
        $totalCredit = $this->nodeValue($workingDocs, 'TotalCredit');

        $actualCount = isset($workingDocs->WorkDocument) ? count($workingDocs->WorkDocument) : 0;

        if ($numberOfEntries !== null && (int) $numberOfEntries !== $actualCount) {
            $this->addError(
                'WORK_DOCS_NUMBER_ENTRIES_MISMATCH',
                "NumberOfEntries ({$numberOfEntries}) não corresponde ao número real de documentos ({$actualCount}).",
                'working_documents',
                'NumberOfEntries'
            );
        }
    }

    protected function validateDocuments(\SimpleXMLElement $workingDocs): void
    {
        if (!isset($workingDocs->WorkDocument)) {
            return;
        }

        $documentNumbers = [];

        foreach ($workingDocs->WorkDocument as $doc) {
            $docNumber = (string) $doc->DocumentNumber;

            if (isset($documentNumbers[$docNumber])) {
                $this->addError(
                    'WORK_DOCS_DUPLICATE',
                    "Documento de trabalho duplicado: {$docNumber}.",
                    'working_documents',
                    'DocumentNumber'
                );
            }
            $documentNumbers[$docNumber] = true;

            $this->validateDocumentStatus($doc, $docNumber);
            $this->validateDocumentDates($doc, $docNumber);
            $this->validateDocumentTotals($doc, $docNumber);
        }

        $this->addInfo(
            'WORK_DOCS_COUNT',
            "Total de documentos de trabalho: " . count($documentNumbers),
            'working_documents'
        );
    }

    protected function validateDocumentStatus(\SimpleXMLElement $doc, string $docNumber): void
    {
        $status = $doc->DocumentStatus;
        if (!$status) {
            $this->addError(
                'WORK_DOCS_STATUS_MISSING',
                "DocumentStatus em falta no documento {$docNumber}.",
                'working_documents',
                'DocumentStatus'
            );
            return;
        }

        $workStatus = $this->nodeValue($status, 'WorkStatus');
        $validStatuses = ['N', 'A', 'F'];
        if ($workStatus !== null && !in_array($workStatus, $validStatuses)) {
            $this->addError(
                'WORK_DOCS_STATUS_INVALID',
                "Estado inválido no documento {$docNumber}: {$workStatus}.",
                'working_documents',
                'WorkStatus'
            );
        }
    }

    protected function validateDocumentDates(\SimpleXMLElement $doc, string $docNumber): void
    {
        $workDate = $this->nodeValue($doc, 'WorkDate');
        $systemEntryDate = $this->nodeValue($doc, 'SystemEntryDate');

        if ($workDate === null) {
            $this->addError(
                'WORK_DOCS_DATE_MISSING',
                "WorkDate em falta no documento {$docNumber}.",
                'working_documents',
                'WorkDate'
            );
        }

        if ($systemEntryDate === null) {
            $this->addError(
                'WORK_DOCS_SYSTEM_DATE_MISSING',
                "SystemEntryDate em falta no documento {$docNumber}.",
                'working_documents',
                'SystemEntryDate'
            );
        }
    }

    protected function validateDocumentTotals(\SimpleXMLElement $doc, string $docNumber): void
    {
        $totals = $doc->DocumentTotals;
        if (!$totals) {
            $this->addError(
                'WORK_DOCS_TOTALS_MISSING',
                "DocumentTotals em falta no documento {$docNumber}.",
                'working_documents',
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
                'WORK_DOCS_TOTALS_MISMATCH',
                "GrossTotal ({$grossTotal}) ≠ NetTotal ({$netTotal}) + TaxPayable ({$taxPayable}) no documento {$docNumber}.",
                'working_documents',
                'GrossTotal'
            );
        }
    }
}
