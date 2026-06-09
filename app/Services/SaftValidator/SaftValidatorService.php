<?php

namespace App\Services\SaftValidator;

use App\Services\SaftValidator\Parsers\SaftParser;
use App\Services\SaftValidator\Rules\Header\HeaderValidator;
use App\Services\SaftValidator\Rules\MasterFiles\GeneralLedgerAccountsValidator;
use App\Services\SaftValidator\Rules\MasterFiles\MasterFilesValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\GeneralLedgerEntriesValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\MovementOfGoodsValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\PaymentsValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\SalesInvoicesValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\WorkingDocumentsValidator;

/**
 * Main SAFT-PT validation orchestrator.
 *
 * Coordinates the full validation pipeline: XSD schema validation followed by
 * business rule checks across all SAFT-PT sections (Header, MasterFiles,
 * SourceDocuments, GeneralLedgerEntries). Each section is handled by a
 * dedicated validator class.
 */
class SaftValidatorService
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $info = [];
    protected ?\SimpleXMLElement $xml = null;

    /**
     * Validate a SAFT-PT file against schema and business rules.
     *
     * First performs XSD schema validation. If blocking errors are found
     * (e.g., malformed XML), returns immediately. Otherwise, runs all
     * section-specific validators and aggregates the results.
     *
     * @param string $filePath Absolute path to the SAFT-PT XML file.
     * @return ValidationResult Aggregated errors, warnings, and info messages.
     */
    public function validate(string $filePath): ValidationResult
    {
        $this->reset();

        $schemaResult = $this->validateSchema($filePath);
        if ($schemaResult->hasBlockingErrors()) {
            return $schemaResult;
        }

        $this->xml = SaftParser::parse($filePath);

        $this->runValidators();

        return new ValidationResult(
            errors: $this->errors,
            warnings: $this->warnings,
            info: $this->info,
        );
    }

    /**
     * Validate the XML file against the official SAFT-PT v1.04_01 XSD schema.
     *
     * @return ValidationResult Schema-level errors (blocking if XML is malformed).
     */
    protected function validateSchema(string $filePath): ValidationResult
    {
        $errors = [];

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        if (!$dom->load($filePath)) {
            $errors[] = ValidationError::blocking(
                'XML_PARSE_ERROR',
                'O ficheiro não é um XML válido.',
                $this->getLibxmlErrors()
            );
            libxml_clear_errors();
            return new ValidationResult(errors: $errors);
        }

        $xsdPath = resource_path('xsd/SAFTPT_1_04_01.xsd');
        if (file_exists($xsdPath) && !$dom->schemaValidate($xsdPath)) {
            foreach (libxml_get_errors() as $error) {
                $errors[] = ValidationError::schema(
                    'XSD_VALIDATION_ERROR',
                    trim($error->message),
                    $error->line
                );
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return new ValidationResult(errors: $errors);
    }

    /**
     * Run all section-specific validators and collect their results.
     */
    protected function runValidators(): void
    {
        $validators = [
            new HeaderValidator($this->xml),
            new MasterFilesValidator($this->xml),
            new GeneralLedgerAccountsValidator($this->xml),
            new SalesInvoicesValidator($this->xml),
            new PaymentsValidator($this->xml),
            new MovementOfGoodsValidator($this->xml),
            new WorkingDocumentsValidator($this->xml),
            new GeneralLedgerEntriesValidator($this->xml),
        ];

        foreach ($validators as $validator) {
            $result = $validator->validate();
            $this->errors = array_merge($this->errors, $result->errors);
            $this->warnings = array_merge($this->warnings, $result->warnings);
            $this->info = array_merge($this->info, $result->info);
        }
    }

    /** Reset internal state for a fresh validation run. */
    protected function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->info = [];
        $this->xml = null;
    }

    /** Collect libxml error messages into a single string for display. */
    protected function getLibxmlErrors(): string
    {
        $messages = [];
        foreach (libxml_get_errors() as $error) {
            $messages[] = "Linha {$error->line}: " . trim($error->message);
        }
        return implode("\n", $messages);
    }
}
