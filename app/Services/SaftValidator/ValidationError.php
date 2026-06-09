<?php

namespace App\Services\SaftValidator;

/**
 * Value object representing a single validation issue found in a SAFT-PT file.
 *
 * Severity levels:
 *  - "error"   : Definite non-compliance. The file will likely be rejected by AT.
 *  - "warning" : Potential issue that warrants manual review but may be acceptable.
 *  - "info"    : Informational note (e.g., document counts, file type summary).
 *
 * Blocking errors (e.g., malformed XML) halt the entire validation pipeline
 * because subsequent validators cannot operate on unparseable input.
 */
class ValidationError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly string $category,
        public readonly string $severity,
        public readonly bool $blocking = false,
        public readonly ?int $line = null,
        public readonly ?string $details = null,
        public readonly ?string $field = null,
    ) {}

    public static function blocking(string $code, string $message, ?string $details = null): self
    {
        return new self(
            code: $code,
            message: $message,
            category: 'schema',
            severity: 'error',
            blocking: true,
            details: $details,
        );
    }

    public static function schema(string $code, string $message, ?int $line = null): self
    {
        return new self(
            code: $code,
            message: $message,
            category: 'schema',
            severity: 'error',
            line: $line,
        );
    }

    public static function error(string $code, string $message, string $category, ?string $field = null, ?string $details = null): self
    {
        return new self(
            code: $code,
            message: $message,
            category: $category,
            severity: 'error',
            field: $field,
            details: $details,
        );
    }

    public static function warning(string $code, string $message, string $category, ?string $field = null, ?string $details = null): self
    {
        return new self(
            code: $code,
            message: $message,
            category: $category,
            severity: 'warning',
            field: $field,
            details: $details,
        );
    }

    public static function info(string $code, string $message, string $category, ?string $field = null): self
    {
        return new self(
            code: $code,
            message: $message,
            category: $category,
            severity: 'info',
            field: $field,
        );
    }
}
