<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_document') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_type') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_status') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_date') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_customer') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_origin') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_destination') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['movements'] as $i => $movement)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                    <td class="px-4 py-3 font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $movement['DocumentNumber'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $movement['MovementType'] }}</td>
                    <td class="px-4 py-3">
                        @php
                            $mBadge = $movement['MovementStatus'] === 'N'
                                ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/40'
                                : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/40';
                        @endphp
                        <span class="inline-flex items-center px-2 py-[3px] rounded-[7px] text-[11.5px] font-semibold border {{ $mBadge }}">
                            {{ $movement['MovementStatus'] === 'N' ? __('saft.status_normal') : ($movement['MovementStatus'] === 'A' ? __('saft.status_cancelled') : $movement['MovementStatus']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ $movement['MovementDate'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $movement['CustomerID'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $movement['ShipFrom'] ?: '-' }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $movement['ShipTo'] ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_movements') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
