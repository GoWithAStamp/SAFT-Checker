<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_account_id') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_account_description') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_opening_debit') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_opening_credit') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_closing_debit') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_closing_credit') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['general_ledger_accounts'] ?? [] as $i => $account)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                    <td class="px-4 py-3 font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $account['AccountID'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $account['AccountDescription'] }}</td>
                    <td class="px-4 py-3 text-right font-mono tabular-nums text-[var(--color-text-dim)] dark:text-[#827c72]">{{ (float)$account['OpeningDebitBalance'] > 0 ? number_format((float)$account['OpeningDebitBalance'], 2, ',', '.') . ' €' : '-' }}</td>
                    <td class="px-4 py-3 text-right font-mono tabular-nums text-[var(--color-text-dim)] dark:text-[#827c72]">{{ (float)$account['OpeningCreditBalance'] > 0 ? number_format((float)$account['OpeningCreditBalance'], 2, ',', '.') . ' €' : '-' }}</td>
                    <td class="px-4 py-3 text-right font-mono tabular-nums text-[var(--color-text-dim)] dark:text-[#827c72]">{{ (float)$account['ClosingDebitBalance'] > 0 ? number_format((float)$account['ClosingDebitBalance'], 2, ',', '.') . ' €' : '-' }}</td>
                    <td class="px-4 py-3 text-right font-mono tabular-nums text-[var(--color-text-dim)] dark:text-[#827c72]">{{ (float)$account['ClosingCreditBalance'] > 0 ? number_format((float)$account['ClosingCreditBalance'], 2, ',', '.') . ' €' : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_accounts') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
