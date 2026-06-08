<?php

namespace App\Services\SaftValidator\Rules;

use App\Services\SaftValidator\ValidationError;
use App\Services\SaftValidator\ValidationResult;

abstract class BaseValidator
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $info = [];

    public function __construct(
        protected \SimpleXMLElement $xml,
    ) {}

    abstract public function validate(): ValidationResult;

    protected function addError(string $code, string $message, string $category, ?string $field = null, ?string $details = null): void
    {
        $this->errors[] = ValidationError::error($code, $message, $category, $field, $details);
    }

    protected function addWarning(string $code, string $message, string $category, ?string $field = null, ?string $details = null): void
    {
        $this->warnings[] = ValidationError::warning($code, $message, $category, $field, $details);
    }

    protected function addInfo(string $code, string $message, string $category, ?string $field = null): void
    {
        $this->info[] = ValidationError::info($code, $message, $category, $field);
    }

    protected function result(): ValidationResult
    {
        return new ValidationResult(
            errors: $this->errors,
            warnings: $this->warnings,
            info: $this->info,
        );
    }

    protected function xpath(string $expression): array
    {
        $namespaces = $this->xml->getNamespaces(true);
        if (!empty($namespaces)) {
            $expression = preg_replace('/(?<!\w)(\w+)(?=[\[\/])/', 'saft:$1', $expression);
            $expression = preg_replace('/^(\w+)$/', 'saft:$1', $expression);
        }

        $result = $this->xml->xpath($expression);
        return $result ?: [];
    }

    protected function nodeValue(\SimpleXMLElement $node, string $child): ?string
    {
        $value = $node->{$child};
        if ($value === null || (string) $value === '') {
            return null;
        }
        return (string) $value;
    }

    protected function isValidNIF(string $nif): bool
    {
        if (!preg_match('/^\d{9}$/', $nif)) {
            return false;
        }

        $firstDigit = (int) $nif[0];
        if (!in_array($firstDigit, [1, 2, 3, 5, 6, 7, 8, 9])) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $nif[$i] * (9 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return $checkDigit === (int) $nif[8];
    }
}
