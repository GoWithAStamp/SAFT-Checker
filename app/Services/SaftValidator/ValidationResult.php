<?php

namespace App\Services\SaftValidator;

/**
 * Immutable value object representing the outcome of a SAFT-PT validation.
 *
 * Groups all issues by severity: errors (must fix), warnings (should review),
 * and info (contextual notes). A result is considered valid only when there
 * are zero errors.
 */
class ValidationResult
{
    public function __construct(
        public readonly array $errors = [],
        public readonly array $warnings = [],
        public readonly array $info = [],
    ) {}

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function hasBlockingErrors(): bool
    {
        foreach ($this->errors as $error) {
            if ($error->blocking) {
                return true;
            }
        }
        return false;
    }

    public function totalIssues(): int
    {
        return count($this->errors) + count($this->warnings);
    }

    public function summary(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => count($this->errors),
            'warnings' => count($this->warnings),
            'info' => count($this->info),
        ];
    }
}
