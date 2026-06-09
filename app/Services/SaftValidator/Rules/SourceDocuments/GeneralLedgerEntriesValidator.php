<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

/**
 * Validates the GeneralLedgerEntries section in accounting SAFT files.
 *
 * Only runs for SAFT files of type C (Contabilidade) or I (Integrada).
 * Enforces the fundamental principle of double-entry bookkeeping: within each
 * transaction, the sum of all debit amounts must equal the sum of all credit
 * amounts. Also validates journal/transaction uniqueness, transaction type codes
 * (N=Normal, R=Regularization, A=Closing, J=Adjustment), period-to-date
 * consistency, and cross-references each AccountID against the chart of accounts
 * defined in GeneralLedgerAccounts.
 */
class GeneralLedgerEntriesValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $taxBasis = (string) ($this->xml->Header->TaxAccountingBasis ?? '');

        // Only validate for accounting SAFTs (C = Contabilidade, I = Integrada)
        if (!in_array($taxBasis, ['C', 'I'])) {
            return $this->result();
        }

        $gle = $this->xml->GeneralLedgerEntries ?? null;
        if (!$gle) {
            $this->addWarning(
                'GLE_MISSING',
                'Secção GeneralLedgerEntries em falta para SAFT de Contabilidade.',
                'general_ledger_entries',
                'GeneralLedgerEntries'
            );
            return $this->result();
        }

        $this->validateTotals($gle);
        $this->validateJournals($gle);

        return $this->result();
    }

    protected function validateTotals(\SimpleXMLElement $gle): void
    {
        $declaredEntries = (int) ($gle->NumberOfEntries ?? 0);
        $declaredTotalDebit = (float) ($gle->TotalDebit ?? 0);
        $declaredTotalCredit = (float) ($gle->TotalCredit ?? 0);

        // Count actual transactions across all journals
        $actualEntries = 0;
        $actualTotalDebit = 0.0;
        $actualTotalCredit = 0.0;

        if (isset($gle->Journal)) {
            foreach ($gle->Journal as $journal) {
                if (isset($journal->Transaction)) {
                    foreach ($journal->Transaction as $transaction) {
                        $actualEntries++;

                        if (isset($transaction->Lines)) {
                            foreach ($transaction->Lines->children() as $line) {
                                $debit = (float) ($line->DebitAmount ?? 0);
                                $credit = (float) ($line->CreditAmount ?? 0);
                                $actualTotalDebit += $debit;
                                $actualTotalCredit += $credit;
                            }
                        }
                    }
                }
            }
        }

        // Validate NumberOfEntries
        if ($declaredEntries !== $actualEntries) {
            $this->addError(
                'GLE_NUMBER_ENTRIES_MISMATCH',
                "NumberOfEntries declarado ({$declaredEntries}) não coincide com o total de transações ({$actualEntries}).",
                'general_ledger_entries',
                'NumberOfEntries'
            );
        }

        // Validate TotalDebit
        if (abs($declaredTotalDebit - $actualTotalDebit) > 0.01) {
            $this->addError(
                'GLE_TOTAL_DEBIT_MISMATCH',
                "TotalDebit declarado (" . number_format($declaredTotalDebit, 2) . ") não coincide com a soma dos débitos (" . number_format($actualTotalDebit, 2) . ").",
                'general_ledger_entries',
                'TotalDebit'
            );
        }

        // Validate TotalCredit
        if (abs($declaredTotalCredit - $actualTotalCredit) > 0.01) {
            $this->addError(
                'GLE_TOTAL_CREDIT_MISMATCH',
                "TotalCredit declarado (" . number_format($declaredTotalCredit, 2) . ") não coincide com a soma dos créditos (" . number_format($actualTotalCredit, 2) . ").",
                'general_ledger_entries',
                'TotalCredit'
            );
        }

        $this->addInfo(
            'GLE_TOTALS',
            "Lançamentos contabilísticos: {$actualEntries} transações, Débito: " . number_format($actualTotalDebit, 2) . "€, Crédito: " . number_format($actualTotalCredit, 2) . "€",
            'general_ledger_entries'
        );
    }

    protected function validateJournals(\SimpleXMLElement $gle): void
    {
        if (!isset($gle->Journal)) {
            return;
        }

        $journalIDs = [];
        $accountIDs = $this->getAccountIDs();

        foreach ($gle->Journal as $journal) {
            $journalID = (string) ($journal->JournalID ?? '');
            $description = (string) ($journal->Description ?? '');

            // Check duplicate JournalID
            if (isset($journalIDs[$journalID])) {
                $this->addError(
                    'GLE_JOURNAL_DUPLICATE',
                    "Diário duplicado: {$journalID}.",
                    'general_ledger_entries',
                    'JournalID'
                );
            }
            $journalIDs[$journalID] = true;

            // JournalID required
            if (empty($journalID)) {
                $this->addError(
                    'GLE_JOURNAL_ID_MISSING',
                    'JournalID em falta para um diário.',
                    'general_ledger_entries',
                    'JournalID'
                );
            }

            // Description required
            if (empty($description)) {
                $this->addWarning(
                    'GLE_JOURNAL_DESC_MISSING',
                    "Descrição em falta para o diário {$journalID}.",
                    'general_ledger_entries',
                    'Description'
                );
            }

            // Validate transactions in this journal
            if (isset($journal->Transaction)) {
                $this->validateTransactions($journal, $journalID, $accountIDs);
            }
        }

        $this->addInfo(
            'GLE_JOURNALS_COUNT',
            "Total de diários: " . count($journalIDs),
            'general_ledger_entries'
        );
    }

    protected function validateTransactions(\SimpleXMLElement $journal, string $journalID, array $accountIDs): void
    {
        $transactionIDs = [];

        foreach ($journal->Transaction as $transaction) {
            $transactionID = (string) ($transaction->TransactionID ?? '');
            $period = (string) ($transaction->Period ?? '');
            $transactionDate = (string) ($transaction->TransactionDate ?? '');
            $systemEntryDate = (string) ($transaction->SystemEntryDate ?? '');
            $description = (string) ($transaction->Description ?? '');
            $transactionType = (string) ($transaction->TransactionType ?? '');

            // Validate TransactionID uniqueness within journal
            if (!empty($transactionID) && isset($transactionIDs[$transactionID])) {
                $this->addError(
                    'GLE_TRANSACTION_DUPLICATE',
                    "Transação duplicada no diário {$journalID}: {$transactionID}.",
                    'general_ledger_entries',
                    'TransactionID'
                );
            }
            if (!empty($transactionID)) {
                $transactionIDs[$transactionID] = true;
            }

            // Validate TransactionType
            $validTypes = ['N', 'R', 'A', 'J'];
            if (!empty($transactionType) && !in_array($transactionType, $validTypes)) {
                $this->addError(
                    'GLE_TRANSACTION_TYPE_INVALID',
                    "TransactionType inválido para transação {$transactionID}: {$transactionType}.",
                    'general_ledger_entries',
                    'TransactionType',
                    'Valores válidos: N (Normal), R (Regularização), A (Apuramento), J (Ajustamento).'
                );
            }

            // Validate Period matches TransactionDate
            if (!empty($period) && !empty($transactionDate)) {
                $this->validatePeriodDate($period, $transactionDate, $transactionID);
            }

            // Validate dates
            if (!empty($transactionDate) && !empty($systemEntryDate)) {
                $txDate = strtotime($transactionDate);
                $sysDate = strtotime($systemEntryDate);

                if ($txDate && $sysDate && $txDate > $sysDate) {
                    $this->addWarning(
                        'GLE_TRANSACTION_DATE_AFTER_SYSTEM',
                        "Data da transação {$transactionID} é posterior à data de sistema.",
                        'general_ledger_entries',
                        'TransactionDate'
                    );
                }
            }

            // Validate transaction lines balance (debits = credits)
            $this->validateTransactionBalance($transaction, $transactionID, $journalID, $accountIDs);
        }
    }

    protected function validatePeriodDate(string $period, string $transactionDate, string $transactionID): void
    {
        $dateParts = explode('-', $transactionDate);
        if (count($dateParts) >= 2) {
            $month = (int) $dateParts[1];
            $declaredPeriod = (int) $period;

            // Periods 1-12 map to calendar months; 13-16 are special closing periods
            // (e.g., period 13 for year-end closing entries per SNC rules)
            if ($declaredPeriod >= 1 && $declaredPeriod <= 12 && $declaredPeriod !== $month) {
                $this->addWarning(
                    'GLE_PERIOD_DATE_MISMATCH',
                    "Período ({$period}) não coincide com o mês da transação {$transactionID} ({$transactionDate}).",
                    'general_ledger_entries',
                    'Period'
                );
            }
        }
    }

    protected function validateTransactionBalance(\SimpleXMLElement $transaction, string $transactionID, string $journalID, array $accountIDs): void
    {
        if (!isset($transaction->Lines)) {
            $this->addError(
                'GLE_TRANSACTION_NO_LINES',
                "Transação {$transactionID} no diário {$journalID} sem linhas.",
                'general_ledger_entries',
                'Lines'
            );
            return;
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $lineCount = 0;

        foreach ($transaction->Lines->children() as $line) {
            $lineCount++;
            $accountID = (string) ($line->AccountID ?? '');
            $debit = (float) ($line->DebitAmount ?? 0);
            $credit = (float) ($line->CreditAmount ?? 0);

            $totalDebit += $debit;
            $totalCredit += $credit;

            // A line must have either DebitAmount or CreditAmount, not both
            if ($debit > 0 && $credit > 0) {
                $this->addError(
                    'GLE_LINE_BOTH_DEBIT_CREDIT',
                    "Linha da transação {$transactionID} tem débito e crédito simultaneamente.",
                    'general_ledger_entries',
                    'DebitAmount/CreditAmount'
                );
            }

            if ($debit == 0 && $credit == 0) {
                $this->addWarning(
                    'GLE_LINE_ZERO_AMOUNT',
                    "Linha da transação {$transactionID} com valor zero.",
                    'general_ledger_entries',
                    'DebitAmount/CreditAmount'
                );
            }

            // Cross-reference AccountID against GeneralLedgerAccounts
            if (!empty($accountID) && !empty($accountIDs) && !isset($accountIDs[$accountID])) {
                $this->addError(
                    'GLE_ACCOUNT_NOT_IN_MASTER',
                    "Conta {$accountID} usada na transação {$transactionID} não existe no plano de contas.",
                    'general_ledger_entries',
                    'AccountID',
                    'Todas as contas referenciadas nos lançamentos devem existir no GeneralLedgerAccounts.'
                );
            }
        }

        // Double-entry bookkeeping: every transaction must balance (debits == credits)
        if (abs($totalDebit - $totalCredit) > 0.01) {
            $this->addError(
                'GLE_TRANSACTION_UNBALANCED',
                "Transação {$transactionID} no diário {$journalID} não está balanceada: Débito=" . number_format($totalDebit, 2) . ", Crédito=" . number_format($totalCredit, 2) . ".",
                'general_ledger_entries',
                'Lines',
                'Cada transação contabilística deve ter débitos iguais aos créditos.'
            );
        }
    }

    /**
     * Get all AccountIDs from GeneralLedgerAccounts for cross-referencing.
     */
    protected function getAccountIDs(): array
    {
        $accountIDs = [];
        $mf = $this->xml->MasterFiles ?? null;

        if ($mf && isset($mf->GeneralLedgerAccounts->Account)) {
            foreach ($mf->GeneralLedgerAccounts->Account as $account) {
                $id = (string) ($account->AccountID ?? '');
                if (!empty($id)) {
                    $accountIDs[$id] = true;
                }
            }
        }

        return $accountIDs;
    }
}
