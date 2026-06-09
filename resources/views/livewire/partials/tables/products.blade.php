<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_type') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_code') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_group') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_description') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_barcode') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['products'] as $i => $product)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                    <td class="px-4 py-3">
                        @php
                            $ptBadge = match($product['ProductType']) {
                                'P' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/40',
                                'S' => 'bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-800/40',
                                'E' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/40',
                                'I' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-400 border-violet-200 dark:border-violet-800/40',
                                default => 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-muted)] dark:text-[#b3ada3] border-[var(--color-border-warm)] dark:border-[#332f2a]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-[3px] rounded-[7px] text-[11.5px] font-semibold border {{ $ptBadge }}">
                            {{ __('saft.product_type_' . $product['ProductType']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $product['ProductCode'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $product['ProductGroup'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $product['ProductDescription'] }}</td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $product['ProductNumberCode'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_products') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
