<?php

namespace App\Services\SaftValidator;

use App\Services\SaftValidator\Parsers\SaftParser;
use App\Services\SaftValidator\Rules\Header\HeaderValidator;
use App\Services\SaftValidator\Rules\MasterFiles\MasterFilesValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\SalesInvoicesValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\PaymentsValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\MovementOfGoodsValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\WorkingDocumentsValidator;
use App\Services\SaftValidator\Rules\MasterFiles\GeneralLedgerAccountsValidator;
use App\Services\SaftValidator\Rules\SourceDocuments\GeneralLedgerEntriesValidator;

class SaftValidatorService
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $info = [];
    protected ?\SimpleXMLElement $xml = null;

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

    protected function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->info = [];
        $this->xml = null;
    }

    protected function getLibxmlErrors(): string
    {
        $messages = [];
        foreach (libxml_get_errors() as $error) {
            $messages[] = "Linha {$error->line}: " . trim($error->message);
        }
        return implode("\n", $messages);
    }
}
