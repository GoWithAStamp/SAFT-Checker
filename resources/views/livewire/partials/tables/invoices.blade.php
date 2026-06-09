<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="w-8"></th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_invoice_no') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_date') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_customer') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_net') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_tax') }}</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_total') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['invoices'] as $i => $invoice)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 cursor-pointer {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }} {{ $invoice['InvoiceStatus'] === 'A' ? 'opacity-60' : '' }}" wire:click="toggleInvoice('{{ $invoice['InvoiceNo'] }}')">
                    <td class="pl-3 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">
                        <svg class="w-4 h-4 transition-transform duration-150 {{ $expandedInvoice === $invoice['InvoiceNo'] ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-semibold px-1.5 py-0.5 rounded text-[10.5px] bg-blue-50 dark:bg-blue-900/15 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">{{ $invoice['InvoiceType'] }}</span>
                            <span class="font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec] {{ $invoice['InvoiceStatus'] === 'A' ? 'line-through' : '' }}">{{ $invoice['InvoiceNo'] }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ $invoice['InvoiceDate'] }}</td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3] {{ $invoice['InvoiceStatus'] === 'A' ? 'line-through' : '' }}">{{ $invoice['CustomerID'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ number_format((float)$invoice['NetTotal'], 2, ',', '.') }} &euro;</td>
                    <td class="px-4 py-3 text-right font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ number_format((float)$invoice['TaxPayable'], 2, ',', '.') }} &euro;</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec] tabular-nums">{{ number_format((float)$invoice['GrossTotal'], 2, ',', '.') }} &euro;</td>
                    <td class="px-4 py-3">
                        @php
                            $sBadge = match($invoice['InvoiceStatus']) {
                                'N' => 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/40',
                                'A' => 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/40',
                                default => 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-muted)] dark:text-[#b3ada3] border-[var(--color-border-warm)] dark:border-[#332f2a]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-[3px] rounded-[7px] text-[11.5px] font-semibold border {{ $sBadge }}">
                            {{ match($invoice['InvoiceStatus']) {
                                'N' => __('saft.status_normal'),
                                'A' => __('saft.status_cancelled'),
                                'S' => __('saft.status_self_billing'),
                                'R' => __('saft.status_summary'),
                                'F' => __('saft.status_billed'),
                                default => $invoice['InvoiceStatus']
                            } }}
                        </span>
                    </td>
                </tr>
                {{-- Expanded Lines --}}
                @if($expandedInvoice === $invoice['InvoiceNo'] && !empty($invoice['Lines']))
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="bg-[var(--color-surface-2)] dark:bg-[#262320] px-8 py-4 border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                                <p class="text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em] mb-2">{{ __('saft.invoice_lines') }}</p>
                                <table class="min-w-full text-[12.5px]">
                                    <thead>
                                        <tr class="text-[10px] text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_line_number') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_code') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_description') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_quantity') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_unit_price') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_amount') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_tax') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_exemption') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice['Lines'] as $line)
                                            <tr class="border-t border-[var(--color-border-warm)] dark:border-[#332f2a]">
                                                <td class="pr-4 py-2 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $line['LineNumber'] }}</td>
                                                <td class="pr-4 py-2 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['ProductCode'] }}</td>
                                                <td class="pr-4 py-2 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['ProductDescription'] }}</td>
                                                <td class="pr-4 py-2 text-right font-mono tabular-nums text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['Quantity'] }}</td>
                                                <td class="pr-4 py-2 text-right font-mono tabular-nums text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['UnitPrice'] ? number_format((float)$line['UnitPrice'], 2, ',', '.') . ' €' : '-' }}</td>
                                                <td class="pr-4 py-2 text-right font-mono tabular-nums font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ ($line['CreditAmount'] ?: $line['DebitAmount']) ? number_format((float)($line['CreditAmount'] ?: $line['DebitAmount']), 2, ',', '.') . ' €' : '-' }}</td>
                                                <td class="pr-4 py-2 text-right">
                                                    @if($line['TaxPercentage'])
                                                        <span class="font-mono font-semibold text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['TaxPercentage'] }}%</span>
                                                    @else
                                                        <span class="text-[var(--color-text-dim)] dark:text-[#827c72]">-</span>
                                                    @endif
                                                </td>
                                                <td class="pr-4 py-2 text-[var(--color-text-dim)] dark:text-[#827c72] text-[11px]">{{ $line['TaxExemptionReason'] ?: ($line['TaxExemptionCode'] ?: '-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_invoices') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
