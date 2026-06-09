<div class="overflow-x-auto">
    <table class="min-w-full text-[13.5px]">
        <thead>
            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                <th class="w-8"></th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_journal') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_transaction_id') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_period') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_date') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_transaction_type') }}</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.col_description') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saftData['general_ledger_entries'] ?? [] as $i => $entry)
                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] hover:bg-blue-50/40 dark:hover:bg-blue-900/10 cursor-pointer {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}" wire:click="toggleTransaction('{{ $entry['TransactionID'] }}')">
                    <td class="pl-3 py-3 text-[var(--color-text-dim)] dark:text-[#827c72]">
                        <svg class="w-4 h-4 transition-transform duration-150 {{ $expandedTransaction === $entry['TransactionID'] ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $entry['JournalID'] }}</td>
                    <td class="px-4 py-3 font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $entry['TransactionID'] }}</td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ $entry['Period'] }}</td>
                    <td class="px-4 py-3 font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3] tabular-nums">{{ $entry['TransactionDate'] }}</td>
                    <td class="px-4 py-3">
                        @if(!empty($entry['TransactionType']))
                            @php
                                $tBadge = match($entry['TransactionType']) {
                                    'N' => 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/40',
                                    'A' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/40',
                                    'R' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/40',
                                    default => 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-muted)] dark:text-[#b3ada3] border-[var(--color-border-warm)] dark:border-[#332f2a]',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-[3px] rounded-[7px] text-[11.5px] font-semibold border {{ $tBadge }}">
                                {{ __('saft.transaction_type_' . $entry['TransactionType']) }}
                            </span>
                        @else
                            <span class="text-[var(--color-text-dim)] dark:text-[#827c72]">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[#b3ada3] max-w-xs truncate">{{ $entry['Description'] }}</td>
                </tr>
                {{-- Expanded Transaction Lines --}}
                @if($expandedTransaction === $entry['TransactionID'] && !empty($entry['Lines']))
                    <tr>
                        <td colspan="7" class="p-0">
                            <div class="bg-[var(--color-surface-2)] dark:bg-[#262320] px-8 py-4 border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                                <p class="text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em] mb-2">{{ __('saft.transaction_lines') }}</p>
                                <table class="min-w-full text-[12.5px]">
                                    <thead>
                                        <tr class="text-[10px] text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_record_id') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_account_id') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_description') }}</th>
                                            <th class="pr-4 py-1 text-left">{{ __('saft.col_source_doc') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_debit') }}</th>
                                            <th class="pr-4 py-1 text-right">{{ __('saft.col_credit') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($entry['Lines'] as $line)
                                            <tr class="border-t border-[var(--color-border-warm)] dark:border-[#332f2a]">
                                                <td class="pr-4 py-2 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $line['RecordID'] ?: '-' }}</td>
                                                <td class="pr-4 py-2 font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $line['AccountID'] }}</td>
                                                <td class="pr-4 py-2 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $line['Description'] ?: '-' }}</td>
                                                <td class="pr-4 py-2 text-[var(--color-text-dim)] dark:text-[#827c72] text-[11px]">{{ $line['SourceDocumentID'] ?: '-' }}</td>
                                                <td class="pr-4 py-2 text-right font-mono tabular-nums {{ !empty($line['DebitAmount']) && (float)$line['DebitAmount'] > 0 ? 'font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]' : 'text-[var(--color-text-dim)] dark:text-[#827c72]' }}">
                                                    {{ !empty($line['DebitAmount']) && (float)$line['DebitAmount'] > 0 ? number_format((float)$line['DebitAmount'], 2, ',', '.') . ' €' : '-' }}
                                                </td>
                                                <td class="pr-4 py-2 text-right font-mono tabular-nums {{ !empty($line['CreditAmount']) && (float)$line['CreditAmount'] > 0 ? 'font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]' : 'text-[var(--color-text-dim)] dark:text-[#827c72]' }}">
                                                    {{ !empty($line['CreditAmount']) && (float)$line['CreditAmount'] > 0 ? number_format((float)$line['CreditAmount'], 2, ',', '.') . ' €' : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.empty_ledger_entries') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
