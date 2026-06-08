<?php

namespace App\Services\SaftValidator;

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
