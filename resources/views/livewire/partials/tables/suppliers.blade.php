<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_id') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_nif') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_name') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_address') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_city') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_country') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['suppliers'] ?? [] as $i => $supplier)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                    <td class="px-4 py-3 font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $supplier['SupplierID'] }}</td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $supplier['SupplierTaxID'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $supplier['CompanyName'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $supplier['AddressDetail'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $supplier['City'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $supplier['Country'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_suppliers') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
