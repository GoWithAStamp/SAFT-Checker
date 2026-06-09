{{-- Status Banner --}}
<div class="rounded-[11px] p-5 mb-5 flex flex-wrap items-center justify-between gap-4 {{ $summary['valid'] ? 'status-banner-ok' : 'status-banner-error' }}">
    <div class="flex items-center gap-3">
        @if($summary['valid'])
            <svg class="w-8 h-8 text-green-500 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-base font-bold text-green-800 dark:text-green-300">{{ __('saft.results_valid') }}</p>
                <p class="text-sm text-green-700/70 dark:text-green-400/70">{{ __('saft.results_no_issues') }}</p>
            </div>
        @else
            <svg class="w-8 h-8 text-red-500 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-base font-bold text-red-800 dark:text-red-300">{{ __('saft.results_invalid') }}</p>
                <p class="text-sm text-red-700/70 dark:text-red-400/70">{{ __('saft.results_fix_before_submit') }}</p>
            </div>
        @endif
    </div>
    <div class="inline-flex items-center gap-2 bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[10px] px-3 py-2">
        <svg class="w-4 h-4 text-[var(--color-text-dim)] dark:text-[#827c72]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <span class="text-xs font-semibold font-mono text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ $fileName }}</span>
    </div>
</div>

{{-- Summary Cards --}}
@php
    $totalIssues = count($result['errors']) + count($result['warnings']) + count($result['info']);
@endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    {{-- Total --}}
    <button wire:click="setValidationFilter(null)" class="stat-card rounded-[11px] border p-4 text-left {{ $validationFilter === null ? 'active-filter border-blue-300 dark:border-blue-700' : 'border-[var(--color-border-warm)] dark:border-[#332f2a]' }} bg-[var(--color-surface)] dark:bg-[#201e1b]">
        <div class="flex items-center justify-between mb-1">
            <p class="text-3xl font-bold tabular-nums font-mono text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $totalIssues }}</p>
        </div>
        <p class="text-[12.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.results_total') }}</p>
    </button>
    {{-- Errors --}}
    <button wire:click="setValidationFilter('errors')" class="stat-card rounded-[11px] border p-4 text-left {{ $validationFilter === 'errors' ? 'active-filter border-red-300 dark:border-red-700' : 'border-[var(--color-border-warm)] dark:border-[#332f2a]' }} bg-[var(--color-surface)] dark:bg-[#201e1b]">
        <div class="flex items-center justify-between mb-1">
            <p class="text-3xl font-bold tabular-nums font-mono text-red-600 dark:text-red-400">{{ $summary['errors'] }}</p>
            <svg class="w-5 h-5 text-red-400/60 dark:text-red-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-[12.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.results_errors') }}</p>
    </button>
    {{-- Warnings --}}
    <button wire:click="setValidationFilter('warnings')" class="stat-card rounded-[11px] border p-4 text-left {{ $validationFilter === 'warnings' ? 'active-filter border-amber-300 dark:border-amber-700' : 'border-[var(--color-border-warm)] dark:border-[#332f2a]' }} bg-[var(--color-surface)] dark:bg-[#201e1b]">
        <div class="flex items-center justify-between mb-1">
            <p class="text-3xl font-bold tabular-nums font-mono text-amber-600 dark:text-amber-400">{{ $summary['warnings'] }}</p>
            <svg class="w-5 h-5 text-amber-400/60 dark:text-amber-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
        <p class="text-[12.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.results_warnings') }}</p>
    </button>
    {{-- Info --}}
    <button wire:click="setValidationFilter('info')" class="stat-card rounded-[11px] border p-4 text-left {{ $validationFilter === 'info' ? 'active-filter border-blue-300 dark:border-blue-700' : 'border-[var(--color-border-warm)] dark:border-[#332f2a]' }} bg-[var(--color-surface)] dark:bg-[#201e1b]">
        <div class="flex items-center justify-between mb-1">
            <p class="text-3xl font-bold tabular-nums font-mono text-blue-600 dark:text-blue-400">{{ count($result['info']) }}</p>
            <svg class="w-5 h-5 text-blue-400/60 dark:text-blue-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-[12.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.results_info') }}</p>
    </button>
</div>

{{-- Issue List --}}
@php
    $filteredIssues = [];
    if ($validationFilter === null || $validationFilter === 'errors') {
        foreach ($result['errors'] as $e) { $filteredIssues[] = array_merge($e, ['_severity' => 'error']); }
    }
    if ($validationFilter === null || $validationFilter === 'warnings') {
        foreach ($result['warnings'] as $w) { $filteredIssues[] = array_merge($w, ['_severity' => 'warning']); }
    }
    if ($validationFilter === null || $validationFilter === 'info') {
        foreach ($result['info'] as $i) { $filteredIssues[] = array_merge($i, ['_severity' => 'info']); }
    }
@endphp

@if(count($filteredIssues) > 0)
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-[17px] font-bold tracking-tight text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ __('saft.results_occurrences') }}</h3>
        <span class="text-[12.5px] text-[var(--color-text-dim)] dark:text-[#827c72]">{{ count($filteredIssues) }} {{ __('saft.results_records') }}</span>
    </div>
    <div class="space-y-2.5">
        @foreach($filteredIssues as $issue)
            @php
                $sev = $issue['_severity'];
                $barClass = match($sev) { 'error' => 'issue-bar-error', 'warning' => 'issue-bar-warning', default => 'issue-bar-info' };
                $pillBg = match($sev) {
                    'error' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                    'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                    default => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                };
                $pillLabel = match($sev) {
                    'error' => __('saft.results_error_label'),
                    'warning' => __('saft.results_warning_label'),
                    default => __('saft.results_info_label')
                };
            @endphp
            <div class="{{ $barClass }} rounded-[11px] bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="inline-flex items-center px-2 py-[3px] rounded-[7px] text-[11px] font-bold shrink-0 {{ $pillBg }}">
                        {{ $pillLabel }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $issue['message'] }}</p>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            @if(!empty($issue['code']))
                                <span class="text-[10.5px] font-mono font-semibold px-1.5 py-0.5 rounded bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $issue['code'] }}</span>
                            @endif
                            @if(!empty($issue['field']))
                                <span class="text-xs text-[var(--color-text-dim)] dark:text-[#827c72] font-mono">{{ $issue['field'] }}</span>
                            @endif
                        </div>
                        @if(!empty($issue['details']))
                            <p class="text-xs text-[var(--color-text-muted)] dark:text-[#b3ada3] mt-2 leading-relaxed">{{ $issue['details'] }}</p>
                        @endif
                        @if(!empty($issue['line']))
                            <p class="text-xs text-[var(--color-text-dim)] dark:text-[#827c72] mt-1">{{ __('saft.results_xml_line') }}: {{ $issue['line'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@elseif(empty($result['errors']) && empty($result['warnings']) && empty($result['info']))
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-green-400 dark:text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ __('saft.results_no_issues') }}</p>
    </div>
@endif
