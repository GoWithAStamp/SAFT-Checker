<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Compares an externally uploaded chart of accounts (Plano de Contas) against the
 * GeneralLedgerAccounts section extracted from a SAFT-PT file.
 *
 * Supports parsing the uploaded file from Excel (.xlsx/.xls) or CSV formats,
 * with flexible header detection that accepts both Portuguese and English column names.
 * The comparison identifies accounts missing from either side, description mismatches,
 * and grouping category differences.
 */
class PlanoContasComparator
{
    /**
     * Parse an uploaded Plano de Contas file (Excel or CSV).
     * Returns array of ['AccountID' => string, 'AccountDescription' => string, 'GroupingCategory' => string|null]
     */
    public static function parse(string $filePath, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'csv') {
            return self::parseCsv($filePath);
        }

        return self::parseExcel($filePath);
    }

    protected static function parseCsv(string $filePath): array
    {
        $accounts = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        // Detect delimiter (semicolon or comma)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        // Read header row
        $header = fgetcsv($handle, 0, $delimiter, '"', "\\");
        if (!$header) {
            fclose($handle);
            return [];
        }

        // Normalize header names
        $headerMap = self::mapHeaders($header);

        if ($headerMap['id'] === null) {
            fclose($handle);
            return [];
        }

        while (($row = fgetcsv($handle, 0, $delimiter, '"', "\\")) !== false) {
            $accountId = trim($row[$headerMap['id']] ?? '');
            if (empty($accountId)) continue;

            $accounts[] = [
                'AccountID' => $accountId,
                'AccountDescription' => trim($row[$headerMap['description']] ?? ''),
                'GroupingCategory' => $headerMap['grouping'] !== null ? trim($row[$headerMap['grouping']] ?? '') : null,
            ];
        }

        fclose($handle);
        return $accounts;
    }

    protected static function parseExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) return [];

        // First row is header
        $header = array_shift($rows);
        $headerMap = self::mapHeaders($header);

        if ($headerMap['id'] === null) return [];

        $accounts = [];
        foreach ($rows as $row) {
            $accountId = trim((string) ($row[$headerMap['id']] ?? ''));
            if (empty($accountId)) continue;

            $accounts[] = [
                'AccountID' => $accountId,
                'AccountDescription' => trim((string) ($row[$headerMap['description']] ?? '')),
                'GroupingCategory' => $headerMap['grouping'] !== null ? trim((string) ($row[$headerMap['grouping']] ?? '')) : null,
            ];
        }

        return $accounts;
    }

    /**
     * Map header columns to expected fields.
     * Flexible: accepts Portuguese and English names, partial matches.
     */
    protected static function mapHeaders(array $header): array
    {
        $map = ['id' => null, 'description' => null, 'grouping' => null];
        $normalized = array_map(fn($h) => mb_strtolower(trim((string) $h)), $header);

        foreach ($normalized as $i => $col) {
            // Account ID
            if ($map['id'] === null && (
                str_contains($col, 'accountid') ||
                str_contains($col, 'account_id') ||
                str_contains($col, 'código') ||
                str_contains($col, 'codigo') ||
                str_contains($col, 'conta') ||
                str_contains($col, 'nº conta') ||
                str_contains($col, 'n conta') ||
                str_contains($col, 'account') ||
                $col === 'id' ||
                $col === 'cod' ||
                $col === 'code'
            )) {
                $map['id'] = $i;
            }

            // Description
            if ($map['description'] === null && (
                str_contains($col, 'descri') ||
                str_contains($col, 'designa') ||
                str_contains($col, 'nome') ||
                str_contains($col, 'name') ||
                str_contains($col, 'designation')
            )) {
                $map['description'] = $i;
            }

            // Grouping category
            if ($map['grouping'] === null && (
                str_contains($col, 'grouping') ||
                str_contains($col, 'categoria') ||
                str_contains($col, 'tipo') ||
                str_contains($col, 'type') ||
                str_contains($col, 'class')
            )) {
                $map['grouping'] = $i;
            }
        }

        // Fallback: if no header matched, try first two columns as ID + Description
        if ($map['id'] === null && count($header) >= 2) {
            // Check if first column looks like account numbers
            $map['id'] = 0;
            $map['description'] = 1;
        }

        return $map;
    }

    /**
     * Compare uploaded Plano de Contas against SAFT GeneralLedgerAccounts.
     *
     * Returns [
     *   'missing_in_saft'   => accounts in plano but not in SAFT,
     *   'extra_in_saft'     => accounts in SAFT but not in plano,
     *   'description_diff'  => accounts with different descriptions,
     *   'grouping_diff'     => accounts with different grouping category,
     *   'matched'           => accounts that match perfectly,
     *   'summary'           => totals
     * ]
     */
    public static function compare(array $planoAccounts, array $saftAccounts): array
    {
        // Index by AccountID
        $planoMap = [];
        foreach ($planoAccounts as $acc) {
            $planoMap[$acc['AccountID']] = $acc;
        }

        $saftMap = [];
        foreach ($saftAccounts as $acc) {
            $saftMap[$acc['AccountID']] = $acc;
        }

        $missingInSaft = [];
        $extraInSaft = [];
        $descriptionDiff = [];
        $groupingDiff = [];
        $matched = [];

        // Check plano accounts against SAFT
        foreach ($planoMap as $id => $planoAcc) {
            if (!isset($saftMap[$id])) {
                $missingInSaft[] = $planoAcc;
                continue;
            }

            $saftAcc = $saftMap[$id];
            $hasIssue = false;

            // Description comparison (case-insensitive, trimmed)
            $planoDesc = mb_strtolower(trim($planoAcc['AccountDescription']));
            $saftDesc = mb_strtolower(trim($saftAcc['AccountDescription']));

            if (!empty($planoDesc) && !empty($saftDesc) && $planoDesc !== $saftDesc) {
                $descriptionDiff[] = [
                    'AccountID' => $id,
                    'PlanoDescription' => $planoAcc['AccountDescription'],
                    'SaftDescription' => $saftAcc['AccountDescription'],
                ];
                $hasIssue = true;
            }

            // Grouping category comparison
            if ($planoAcc['GroupingCategory'] !== null && !empty($planoAcc['GroupingCategory'])) {
                $planoGroup = mb_strtoupper(trim($planoAcc['GroupingCategory']));
                $saftGroup = mb_strtoupper(trim($saftAcc['GroupingCategory'] ?? ''));

                if ($planoGroup !== $saftGroup) {
                    $groupingDiff[] = [
                        'AccountID' => $id,
                        'PlanoGrouping' => $planoAcc['GroupingCategory'],
                        'SaftGrouping' => $saftAcc['GroupingCategory'] ?? '',
                    ];
                    $hasIssue = true;
                }
            }

            if (!$hasIssue) {
                $matched[] = $id;
            }
        }

        // Check SAFT accounts not in plano
        foreach ($saftMap as $id => $saftAcc) {
            if (!isset($planoMap[$id])) {
                $extraInSaft[] = $saftAcc;
            }
        }

        return [
            'missing_in_saft' => $missingInSaft,
            'extra_in_saft' => $extraInSaft,
            'description_diff' => $descriptionDiff,
            'grouping_diff' => $groupingDiff,
            'matched' => $matched,
            'summary' => [
                'plano_total' => count($planoMap),
                'saft_total' => count($saftMap),
                'matched' => count($matched),
                'missing_in_saft' => count($missingInSaft),
                'extra_in_saft' => count($extraInSaft),
                'description_diff' => count($descriptionDiff),
                'grouping_diff' => count($groupingDiff),
            ],
        ];
    }
}
