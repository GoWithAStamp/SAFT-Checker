<div class="space-y-5">
    {{-- Upload area --}}
    <div class="card-warm p-6 rounded-[14px]">
        <div class="flex items-start gap-4">
            <div class="w-[46px] h-[46px] rounded-xl flex items-center justify-center bg-[color-mix(in_srgb,var(--color-accent)_14%,var(--color-surface))] text-blue-600 dark:text-blue-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-[15px] font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ __('saft.plano_title') }}</h3>
                <p class="text-[13px] text-[var(--color-text-dim)] dark:text-[#827c72] mt-1">{{ __('saft.plano_subtitle') }}</p>

                <div class="mt-4">
                    <label class="relative inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-[10px] hover:bg-blue-700 transition-colors shadow-[0_1px_2px_rgba(37,99,235,0.4)] cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('saft.plano_upload_label') }}
                        <input type="file" wire:model="planoFile" accept=".xlsx,.xls,.csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </label>

                    @if($planoFileName)
                        <span class="ml-3 text-[13px] text-[var(--color-text-muted)] dark:text-[#b3ada3]">
                            {{ __('saft.plano_file') }}: {{ $planoFileName }}
                        </span>
                    @endif
                </div>

                <p class="text-[12px] text-[var(--color-text-dim)] dark:text-[#6b665e] mt-2">{{ __('saft.plano_upload_hint') }}</p>

                <div class="mt-3 flex items-center gap-3">
                    <span class="text-[12px] text-[var(--color-text-dim)] dark:text-[#6b665e]">{{ __('saft.plano_download_example') }}:</span>
                    <a href="/examples/plano_contas_exemplo.xlsx" download class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Excel (.xlsx)
                    </a>
                    <a href="/examples/plano_contas_exemplo.csv" download class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        CSV (.csv)
                    </a>
                </div>

                @if($planoError)
                    <div class="mt-3 px-4 py-2.5 rounded-[10px] bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 text-red-700 dark:text-red-400 text-[13px]">
                        {{ $planoError }}
                    </div>
                @endif

                <div wire:loading wire:target="planoFile" class="mt-3 flex items-center gap-2 text-[13px] text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    A processar...
                </div>
            </div>
        </div>
    </div>

    {{-- Results --}}
    @if($planoComparison)
        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @php
                $stats = [
                    ['key' => 'plano_total', 'label' => __('saft.plano_your_accounts'), 'color' => 'blue', 'filter' => null],
                    ['key' => 'saft_total', 'label' => __('saft.plano_saft_accounts'), 'color' => 'blue', 'filter' => null],
                    ['key' => 'matched', 'label' => __('saft.plano_matched'), 'color' => 'emerald', 'filter' => 'matched'],
                    ['key' => 'missing_in_saft', 'label' => __('saft.plano_missing_in_saft'), 'color' => 'red', 'filter' => 'missing'],
                    ['key' => 'extra_in_saft', 'label' => __('saft.plano_extra_in_saft'), 'color' => 'amber', 'filter' => 'extra'],
                    ['key' => 'description_diff', 'label' => __('saft.plano_desc_diff'), 'color' => 'orange', 'filter' => 'desc'],
                    ['key' => 'grouping_diff', 'label' => __('saft.plano_grouping_diff'), 'color' => 'purple', 'filter' => 'grouping'],
                ];
            @endphp

            @foreach($stats as $stat)
                <button
                    @if($stat['filter'])
                        wire:click="setPlanoFilter('{{ $stat['filter'] }}')"
                    @endif
                    class="card-warm p-4 rounded-[12px] text-left transition-all {{ $stat['filter'] && $planoFilter === $stat['filter'] ? 'ring-2 ring-blue-500 dark:ring-blue-400' : '' }} {{ $stat['filter'] ? 'hover:ring-1 hover:ring-blue-300 dark:hover:ring-blue-600 cursor-pointer' : '' }}">
                    <div class="text-2xl font-extrabold text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400">
                        {{ $planoComparison['summary'][$stat['key']] }}
                    </div>
                    <div class="text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] mt-1 leading-tight">
                        {{ $stat['label'] }}
                    </div>
                </button>
            @endforeach
        </div>

        {{-- Filter bar --}}
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $filters = [
                    'all' => __('saft.plano_filter_all'),
                    'missing' => __('saft.plano_filter_missing'),
                    'extra' => __('saft.plano_filter_extra'),
                    'desc' => __('saft.plano_filter_desc'),
                    'grouping' => __('saft.plano_filter_grouping'),
                    'matched' => __('saft.plano_filter_matched'),
                ];
            @endphp
            @foreach($filters as $key => $label)
                <button wire:click="setPlanoFilter('{{ $key }}')"
                    class="px-3 py-1.5 text-[12px] font-semibold rounded-lg transition-all
                    {{ $planoFilter === $key
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                    {{ $label }}
                </button>
            @endforeach

            <div class="ml-auto">
                <button wire:click="clearPlanoComparison" class="text-[12px] font-semibold text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors">
                    {{ __('saft.plano_clear') }}
                </button>
            </div>
        </div>

        {{-- Results table --}}
        <div class="card-warm rounded-[14px] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                            <th class="px-4 py-3 text-left font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.plano_col_account') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.plano_col_plano_desc') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.plano_col_saft_desc') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.plano_col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-border-warm)] dark:divide-[#2e2b27]">
                        @php
                            $rows = [];

                            if ($planoFilter === 'all' || $planoFilter === 'matched') {
                                foreach ($planoComparison['matched'] as $id) {
                                    $saftAcc = collect($saftData['general_ledger_accounts'])->firstWhere('AccountID', $id);
                                    $rows[] = [
                                        'id' => $id,
                                        'plano_desc' => $saftAcc['AccountDescription'] ?? '',
                                        'saft_desc' => $saftAcc['AccountDescription'] ?? '',
                                        'status' => 'ok',
                                    ];
                                }
                            }

                            if ($planoFilter === 'all' || $planoFilter === 'missing') {
                                foreach ($planoComparison['missing_in_saft'] as $acc) {
                                    $rows[] = [
                                        'id' => $acc['AccountID'],
                                        'plano_desc' => $acc['AccountDescription'],
                                        'saft_desc' => '—',
                                        'status' => 'missing',
                                    ];
                                }
                            }

                            if ($planoFilter === 'all' || $planoFilter === 'extra') {
                                foreach ($planoComparison['extra_in_saft'] as $acc) {
                                    $rows[] = [
                                        'id' => $acc['AccountID'],
                                        'plano_desc' => '—',
                                        'saft_desc' => $acc['AccountDescription'],
                                        'status' => 'extra',
                                    ];
                                }
                            }

                            if ($planoFilter === 'all' || $planoFilter === 'desc') {
                                foreach ($planoComparison['description_diff'] as $diff) {
                                    $rows[] = [
                                        'id' => $diff['AccountID'],
                                        'plano_desc' => $diff['PlanoDescription'],
                                        'saft_desc' => $diff['SaftDescription'],
                                        'status' => 'desc_diff',
                                    ];
                                }
                            }

                            if ($planoFilter === 'all' || $planoFilter === 'grouping') {
                                foreach ($planoComparison['grouping_diff'] as $diff) {
                                    $rows[] = [
                                        'id' => $diff['AccountID'],
                                        'plano_desc' => $diff['PlanoGrouping'],
                                        'saft_desc' => $diff['SaftGrouping'],
                                        'status' => 'group_diff',
                                    ];
                                }
                            }

                            // Sort by AccountID
                            usort($rows, fn($a, $b) => strnatcmp($a['id'], $b['id']));
                        @endphp

                        @forelse($rows as $row)
                            <tr class="hover:bg-[var(--color-surface-3)] dark:hover:bg-[#252320] transition-colors">
                                <td class="px-4 py-2.5 font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $row['id'] }}</td>
                                <td class="px-4 py-2.5 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $row['plano_desc'] }}</td>
                                <td class="px-4 py-2.5 text-[var(--color-text-muted)] dark:text-[#b3ada3]
                                    {{ $row['status'] === 'desc_diff' ? 'bg-orange-50/50 dark:bg-orange-900/10' : '' }}">{{ $row['saft_desc'] }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($row['status'] === 'ok')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('saft.plano_status_ok') }}
                                        </span>
                                    @elseif($row['status'] === 'missing')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                            {{ __('saft.plano_status_missing') }}
                                        </span>
                                    @elseif($row['status'] === 'extra')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            {{ __('saft.plano_status_extra') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                            {{ __('saft.plano_status_diff') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-[var(--color-text-dim)] dark:text-[#827c72]">
                                    Sem resultados para este filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
