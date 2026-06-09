<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_type') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_region') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_code') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_description') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_rate') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_validity') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['tax_table'] as $i => $tax)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                    <td class="px-4 py-3 font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $tax['TaxType'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $tax['TaxCountryRegion'] }}</td>
                    <td class="px-4 py-3">
                        <span class="font-mono font-semibold px-1.5 py-0.5 rounded bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-muted)] dark:text-[#b3ada3] text-[11.5px]">{{ $tax['TaxCode'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $tax['Description'] }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec] tabular-nums">{{ $tax['TaxPercentage'] }}%</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $tax['TaxExpirationDate'] ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_tax_table') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
