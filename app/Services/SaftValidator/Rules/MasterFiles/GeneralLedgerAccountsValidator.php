<?php

namespace App\Services\SaftValidator\Rules\MasterFiles;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

/**
 * Validates the GeneralLedgerAccounts section (chart of accounts) in accounting SAFT files.
 *
 * Only runs for SAFT files of type C (Contabilidade) or I (Integrada).
 * Validates account structure per the SNC (Sistema de Normalização Contabilística):
 *  - GroupingCategory codes: GR/GA/GM (general) and AR/AA/AM (analytical)
 *  - TaxonomyReference per Portaria 302/2016
 *  - Balance integrity (debit and credit cannot both be non-zero simultaneously)
 *  - TaxonomyCode format (numeric, typically 1-999)
 */
class GeneralLedgerAccountsValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $taxBasis = (string) ($this->xml->Header->TaxAccountingBasis ?? '');

        // Only validate for accounting SAFTs (C = Contabilidade, I = Integrada)
        if (!in_array($taxBasis, ['C', 'I'])) {
            return $this->result();
        }

        $masterFiles = $this->xml->MasterFiles;

        if (!$masterFiles || !isset($masterFiles->GeneralLedgerAccounts)) {
            $this->addError(
                'GL_ACCOUNTS_MISSING',
                'Secção GeneralLedgerAccounts obrigatória para SAFT de Contabilidade.',
                'general_ledger_accounts',
                'GeneralLedgerAccounts'
            );
            return $this->result();
        }

        $gla = $masterFiles->GeneralLedgerAccounts;

        $this->validateTaxonomyReference($gla);
        $this->validateAccounts($gla);

        return $this->result();
    }

    protected function validateTaxonomyReference(\SimpleXMLElement $gla): void
    {
        $taxonomyRef = (string) ($gla->TaxonomyReference ?? '');

        if (empty($taxonomyRef)) {
            $this->addWarning(
                'GL_TAXONOMY_REF_MISSING',
                'TaxonomyReference em falta no plano de contas.',
                'general_ledger_accounts',
                'TaxonomyReference',
                'A referência à taxonomia SNC é recomendada (Portaria 302/2016).'
            );
        }
    }

    protected function validateAccounts(\SimpleXMLElement $gla): void
    {
        if (!isset($gla->Account)) {
            $this->addError(
                'GL_ACCOUNTS_EMPTY',
                'Plano de contas sem nenhuma conta definida.',
                'general_ledger_accounts',
                'Account'
            );
            return;
        }

        $accountIDs = [];
        $accountCount = 0;

        foreach ($gla->Account as $account) {
            $accountCount++;
            $accountID = (string) ($account->AccountID ?? '');
            $accountDescription = (string) ($account->AccountDescription ?? '');
            $groupingCategory = (string) ($account->GroupingCategory ?? '');

            // Check for duplicate AccountID
            if (isset($accountIDs[$accountID])) {
                $this->addError(
                    'GL_ACCOUNT_DUPLICATE',
                    "Conta duplicada: {$accountID}.",
                    'general_ledger_accounts',
                    'AccountID'
                );
            }
            $accountIDs[$accountID] = true;

            // Validate required fields
            if (empty($accountID)) {
                $this->addError(
                    'GL_ACCOUNT_ID_MISSING',
                    'AccountID em falta para uma conta.',
                    'general_ledger_accounts',
                    'AccountID'
                );
            }

            if (empty($accountDescription)) {
                $this->addWarning(
                    'GL_ACCOUNT_DESC_MISSING',
                    "Descrição em falta para a conta {$accountID}.",
                    'general_ledger_accounts',
                    'AccountDescription'
                );
            }

            // Validate GroupingCategory
            $validCategories = ['GR', 'GA', 'GM', 'AR', 'AA', 'AM'];
            if (!empty($groupingCategory) && !in_array($groupingCategory, $validCategories)) {
                $this->addError(
                    'GL_ACCOUNT_CATEGORY_INVALID',
                    "GroupingCategory inválida para a conta {$accountID}: {$groupingCategory}.",
                    'general_ledger_accounts',
                    'GroupingCategory',
                    'Valores válidos: GR (Conta de 1.º grau da contabilidade geral), GA (Conta agregadora da contabilidade geral), GM (Conta de movimento da contabilidade geral), AR (Conta de 1.º grau da contabilidade analítica), AA (Conta agregadora da contabilidade analítica), AM (Conta de movimento da contabilidade analítica).'
                );
            }

            // Validate opening/closing balances for movement accounts (GM/AM)
            if (in_array($groupingCategory, ['GM', 'AM'])) {
                $this->validateAccountBalances($account, $accountID);
            }

            // Validate TaxonomyCode if present
            $taxonomyCode = (string) ($account->TaxonomyCode ?? '');
            if (!empty($taxonomyCode)) {
                $this->validateTaxonomyCode($taxonomyCode, $accountID);
            }
        }

        $this->addInfo(
            'GL_ACCOUNTS_COUNT',
            "Total de contas no plano: {$accountCount}",
            'general_ledger_accounts'
        );
    }

    protected function validateAccountBalances(\SimpleXMLElement $account, string $accountID): void
    {
        $openingDebit = (float) ($account->OpeningDebitBalance ?? 0);
        $openingCredit = (float) ($account->OpeningCreditBalance ?? 0);
        $closingDebit = (float) ($account->ClosingDebitBalance ?? 0);
        $closingCredit = (float) ($account->ClosingCreditBalance ?? 0);

        // Both opening debit and credit can't be non-zero simultaneously
        if ($openingDebit > 0 && $openingCredit > 0) {
            $this->addError(
                'GL_ACCOUNT_OPENING_BALANCE_INVALID',
                "Conta {$accountID}: saldo de abertura não pode ter débito e crédito simultaneamente.",
                'general_ledger_accounts',
                'OpeningDebitBalance/OpeningCreditBalance'
            );
        }

        // Both closing debit and credit can't be non-zero simultaneously
        if ($closingDebit > 0 && $closingCredit > 0) {
            $this->addError(
                'GL_ACCOUNT_CLOSING_BALANCE_INVALID',
                "Conta {$accountID}: saldo de fecho não pode ter débito e crédito simultaneamente.",
                'general_ledger_accounts',
                'ClosingDebitBalance/ClosingCreditBalance'
            );
        }
    }

    protected function validateTaxonomyCode(string $code, string $accountID): void
    {
        // SNC taxonomy codes are numeric, typically 1-999
        if (!preg_match('/^\d{1,10}$/', $code)) {
            $this->addWarning(
                'GL_TAXONOMY_CODE_INVALID',
                "TaxonomyCode inválido para a conta {$accountID}: {$code}.",
                'general_ledger_accounts',
                'TaxonomyCode'
            );
        }
    }
}
