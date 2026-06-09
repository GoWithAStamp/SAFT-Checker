<?php

namespace App\Services\SaftValidator\Rules;

use App\Services\SaftValidator\ValidationError;
use App\Services\SaftValidator\ValidationResult;

/**
 * Abstract base class for all SAFT-PT section validators.
 *
 * Provides a pipeline pattern: each concrete validator implements validate(),
 * collects issues via addError/addWarning/addInfo, then returns a ValidationResult
 * through result(). Also provides shared utilities for XML node access, XPath
 * queries with namespace handling, and Portuguese NIF validation.
 */
abstract class BaseValidator
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $info = [];

    public function __construct(
        protected \SimpleXMLElement $xml,
    ) {}

    /** Run all validation checks for this section and return the results. */
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

    /**
     * Execute an XPath query with automatic namespace prefix injection.
     *
     * If the XML declares a default namespace, element names in the expression
     * are automatically prefixed with "saft:" to match the registered namespace.
     */
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

    /**
     * Validate a Portuguese Tax Identification Number (NIF).
     *
     * Per Portuguese tax law, a NIF is exactly 9 digits. The first digit indicates
     * the taxpayer type (1-3: individuals, 5: collective entities, 6: public bodies,
     * 7: non-residents, 8: sole traders, 9: irregular/temporary NIFs).
     *
     * The last digit is a check digit computed using a weighted mod-11 algorithm:
     * sum(digit[i] * (9 - i)) for i=0..7, then check = (11 - sum % 11) or 0 if < 2.
     */
    protected function isValidNIF(string $nif): bool
    {
        if (!preg_match('/^\d{9}$/', $nif)) {
            return false;
        }

        // Valid first digits per AT (Autoridade Tributaria) rules
        $firstDigit = (int) $nif[0];
        if (!in_array($firstDigit, [1, 2, 3, 5, 6, 7, 8, 9])) {
            return false;
        }

        // Weighted sum for mod-11 check digit calculation
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $nif[$i] * (9 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return $checkDigit === (int) $nif[8];
    }
}
